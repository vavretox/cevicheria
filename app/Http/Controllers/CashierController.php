<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderAudit;
use App\Models\Product;
use App\Models\User;
use App\Services\KitchenPrintService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CashierController extends Controller
{
    public function dashboard(OrderWorkflowService $workflow)
    {
        $currentCashSession = CashSessionController::currentOpenSessionForAuthenticatedUser();

        $pendingOrders = Order::where('status', 'pending')
            ->with(['user', 'details.product', 'diningTable', 'latestAudit.user'])
            ->latest()
            ->get()
            ->map(function (Order $order) {
                $latestAudit = $order->latestAudit;
                $beforeTable = data_get($latestAudit?->meta, 'before.table_number');
                $afterTable = data_get($latestAudit?->meta, 'after.table_number');

                $order->dashboard_has_recent_update = $latestAudit
                    && $latestAudit->action === 'updated'
                    && $latestAudit->created_at?->greaterThan(now()->subMinutes(30));

                $order->dashboard_last_update_label = $order->dashboard_has_recent_update
                    ? 'Actualizado ' . $latestAudit->created_at->format('H:i')
                    : null;

                $order->dashboard_table_change = $latestAudit
                    && $latestAudit->action === 'updated'
                    && $beforeTable
                    && $afterTable
                    && $beforeTable !== $afterTable;

                $order->dashboard_table_change_label = $order->dashboard_table_change
                    ? $beforeTable . ' -> ' . $afterTable
                    : null;

                return $order;
            });

        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock']);
        $availableTables = $workflow->getSelectableTables();
        $tableBoard = $workflow->getTableBoard();
        $waiters = User::where('role', 'mesero')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'order_channel']);

        $todayOrders = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        $todaySales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');

        return view('cashier.dashboard', compact('pendingOrders', 'todayOrders', 'todaySales', 'products', 'waiters', 'availableTables', 'tableBoard', 'currentCashSession'));
    }

    public function tables()
    {
        $tableBoard = DiningTable::query()
            ->withCount('activeOrders')
            ->with(['activeOrders' => function ($query) {
                $query->with('user')->latest();
            }])
            ->orderByRaw('COALESCE(zone, "")')
            ->orderBy('name')
            ->get(['id', 'name', 'zone', 'active', 'reservation_name', 'reservation_at']);

        return view('cashier.tables', compact('tableBoard'));
    }

    public function showOrder($id, OrderWorkflowService $workflow)
    {
        $order = Order::with(['user', 'details.product', 'audits.user', 'diningTable'])
            ->findOrFail($id);

        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock']);
        $waiters = User::where('role', 'mesero')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'order_channel']);
        $selectableTables = $workflow->getSelectableTables($order);

        $audits = $order->audits()->with('user')->latest()->take(50)->get();
        $productNameMap = $this->buildAuditProductNameMap($order, $audits);
        $auditEntries = $audits->map(fn (OrderAudit $audit) => $this->formatAuditEntry($audit, $productNameMap))->values();

        return view('cashier.order-detail', compact('order', 'products', 'waiters', 'audits', 'auditEntries', 'selectableTables'));
    }

    public function orderSummary($id)
    {
        $order = Order::with(['user', 'cashier', 'details.product'])
            ->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'display_number' => $order->display_number,
            'table' => $order->table_number ?: 'Delivery',
            'service_mode' => $order->service_mode,
            'service_mode_label' => $order->service_mode_label,
            'status' => $order->status,
            'waiter' => $order->user?->name ?? '-',
            'cashier' => $order->cashier?->name ?? '-',
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'total' => number_format((float) $order->total, 2, '.', ''),
            'details' => $order->details->map(fn ($detail) => [
                'product_name' => $detail->product?->name ?? 'Producto',
                'quantity' => (int) $detail->quantity,
                'unit_price' => number_format((float) $detail->unit_price, 2, '.', ''),
                'subtotal' => number_format((float) $detail->subtotal, 2, '.', ''),
                'notes' => $detail->notes,
                'service_type' => $detail->service_type,
                'service_type_label' => $detail->service_type_label,
            ])->values(),
        ]);
    }

    public function processOrder(Request $request, $id)
    {
        try {
            $currentCashSession = CashSessionController::currentOpenSessionForAuthenticatedUser();
            if (!$currentCashSession) {
                return redirect()->back()->with('error', 'Debes abrir caja antes de procesar y cobrar pedidos.');
            }

            $request->validate([
                'payment_method' => 'required|in:cash,qr,mixed',
                'cash_paid_amount' => 'nullable|numeric|min:0',
                'qr_paid_amount' => 'nullable|numeric|min:0',
            ]);

            $order = Order::with(['details.product'])->findOrFail($id);

            if ($order->status !== 'pending') {
                return redirect()->back()->with('error', 'Esta orden ya fue procesada');
            }

            DB::beginTransaction();

            foreach ($order->details as $detail) {
                $product = $detail->product;
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Producto no encontrado en el pedido.');
                }
                if ($product->stock < $detail->quantity) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Stock insuficiente para ' . $product->name);
                }
            }

            foreach ($order->details as $detail) {
                $detail->product->decrement('stock', $detail->quantity);
            }

            $paymentMethod = $request->payment_method;
            $cashPaidAmount = 0.0;
            $qrPaidAmount = 0.0;
            $amountReceived = 0.0;
            $changeAmount = 0.0;

            if ($paymentMethod === 'cash') {
                $amountReceived = (float) $order->total;
                $cashPaidAmount = $amountReceived;
            } elseif ($paymentMethod === 'qr') {
                $qrPaidAmount = (float) $order->total;
                $amountReceived = (float) $order->total;
            } else {
                $cashPaidAmount = (float) $request->input('cash_paid_amount', 0);
                $qrPaidAmount = (float) $request->input('qr_paid_amount', 0);
                $mixedTotal = round($cashPaidAmount + $qrPaidAmount, 2);
                $orderTotal = round((float) $order->total, 2);

                if ($cashPaidAmount <= 0 || $qrPaidAmount <= 0) {
                    return redirect()->back()->with('error', 'En pago mixto debes registrar un monto mayor a cero para efectivo y QR.');
                }

                if (abs($mixedTotal - $orderTotal) > 0.01) {
                    return redirect()->back()->with('error', 'La suma de efectivo y QR debe ser igual al total del pedido.');
                }

                $amountReceived = $mixedTotal;
            }

            $order->update([
                'status' => 'completed',
                'cashier_id' => Auth::id(),
                'cash_session_id' => $currentCashSession->id,
                'payment_method' => $paymentMethod,
                'amount_received' => $amountReceived,
                'cash_paid_amount' => $cashPaidAmount,
                'qr_paid_amount' => $qrPaidAmount,
                'change_amount' => $changeAmount,
                'completed_at' => now(),
            ]);

            $this->logAudit($order->id, 'completed', [
                'details_count' => $order->details->count(),
                'total' => $order->total,
                'payment_method' => $paymentMethod,
                'amount_received' => $amountReceived,
                'cash_paid_amount' => $cashPaidAmount,
                'qr_paid_amount' => $qrPaidAmount,
                'change_amount' => $changeAmount,
            ]);

            DB::commit();

            return redirect()->route('cashier.print-receipt', $order->id)
                ->with('success', 'Orden procesada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar pedido', ['order_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al procesar el pedido. Intenta nuevamente.');
        }
    }

    public function createOrder(Request $request, OrderWorkflowService $workflow)
    {
        $request->validate([
            'table_id' => 'nullable|integer|exists:tables,id',
            'waiter_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'items.*.service_type' => 'nullable|in:dine_in,takeaway',
        ]);

        try {
            $waiter = User::findOrFail($request->integer('waiter_id'));
            $order = $workflow->createOrder(
                $waiter,
                collect($request->items),
                $request->filled('table_id') ? $request->integer('table_id') : null,
                Auth::id(),
                true
            );

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'order_id' => $order->id,
                'display_number' => $order->display_number,
                'print_kitchen_url' => route('cashier.print-kitchen-order', $order->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear pedido (caja)', ['error' => $e->getMessage(), 'payload' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pedido: ' . $e->getMessage(),
            ], $e instanceof \RuntimeException ? 422 : 500);
        }
    }

    public function updateOrderItems(Request $request, $id, OrderWorkflowService $workflow)
    {
        $request->validate([
            'table_id' => 'nullable|integer|exists:tables,id',
            'waiter_id' => 'nullable|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'items.*.service_type' => 'nullable|in:dine_in,takeaway',
        ]);

        $order = Order::where('status', 'pending')
            ->with('details')
            ->findOrFail($id);

        try {
            $selectedWaiter = $request->filled('waiter_id')
                ? User::findOrFail($request->integer('waiter_id'))
                : $order->user;
            $workflow->updateOrder(
                $order,
                $selectedWaiter,
                collect($request->items),
                $request->filled('table_id') ? $request->integer('table_id') : null,
                Auth::id(),
                true
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar pedido (caja)', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pedido: ' . $e->getMessage(),
            ], $e instanceof \RuntimeException ? 422 : 500);
        }
    }

    public function printKitchenOrder($id, KitchenPrintService $kitchenPrint)
    {
        $order = Order::with(['user', 'details.product.category'])
            ->findOrFail($id);
        return view('waiter.print-order', array_merge(
            ['order' => $order],
            $kitchenPrint->buildMainPrintPayload($order)
        ));
    }

    public function cancelOrder($id)
    {
        try {
            $order = Order::where('status', 'pending')->findOrFail($id);
            $order->update(['status' => 'cancelled']);
            $this->logAudit($order->id, 'cancelled', []);
            return redirect()->back()->with('success', 'Pedido cancelado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al cancelar pedido', ['order_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'No se pudo cancelar el pedido.');
        }
    }

    public function revertOrder($id)
    {
        try {
            $order = Order::with(['details.product', 'diningTable'])->where('status', 'completed')->findOrFail($id);
            request()->validate([
                'reason' => 'required|string|max:255',
            ]);

            if ($order->table_id) {
                $this->resolveTableForOrder($order->table_id);
            }

            DB::beginTransaction();
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                }
            }

            $order->update([
                'status' => 'pending',
                'cashier_id' => null,
                'cash_session_id' => null,
                'payment_method' => null,
                'amount_received' => null,
                'cash_paid_amount' => null,
                'qr_paid_amount' => null,
                'change_amount' => null,
                'completed_at' => null,
                'revert_reason' => request('reason'),
            ]);

            $this->logAudit($order->id, 'reverted', [
                'reason' => request('reason'),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Venta revertida a pendiente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al revertir venta', ['order_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'No se pudo revertir la venta.');
        }
    }

    private function logAudit(int $orderId, string $action, array $meta): void
    {
        OrderAudit::create([
            'order_id' => $orderId,
            'user_id' => Auth::id(),
            'action' => $action,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    private function formatAuditEntry(OrderAudit $audit, array $productNameMap): array
    {
        $meta = $audit->meta ?? [];
        $title = ucfirst($audit->action);
        $details = [];

        if ($audit->action === 'created') {
            $title = 'Pedido creado';
            $details[] = 'Mesa inicial: ' . ($meta['table_number'] ?? '-');
            $details = array_merge($details, $this->summarizeAuditItems($meta['items'] ?? [], 'Agregado', $productNameMap));
        } elseif ($audit->action === 'updated') {
            $title = 'Pedido actualizado';
            $before = $meta['before'] ?? [];
            $after = $meta['after'] ?? [];

            $beforeTable = $before['table_number'] ?? null;
            $afterTable = $after['table_number'] ?? null;
            if ($beforeTable && $afterTable && $beforeTable !== $afterTable) {
                $details[] = 'Cambio de mesa: ' . $beforeTable . ' -> ' . $afterTable;
            }

            $details = array_merge($details, $this->buildItemDiffDetails(
                $before['items'] ?? [],
                $after['items'] ?? [],
                $productNameMap
            ));
        } elseif ($audit->action === 'completed') {
            $title = 'Pedido cobrado';
            if (isset($meta['total'])) {
                $details[] = 'Total cobrado: Bs. ' . number_format((float) $meta['total'], 2);
            }
            if (!empty($meta['payment_method'])) {
                $details[] = 'Metodo de pago: ' . match ($meta['payment_method']) {
                    'cash' => 'Efectivo',
                    'mixed' => 'Efectivo + QR',
                    default => 'QR',
                };
            }
            if (isset($meta['amount_received']) && $meta['payment_method'] === 'cash') {
                $details[] = 'Recibido: Bs. ' . number_format((float) $meta['amount_received'], 2);
            }
            if (isset($meta['cash_paid_amount']) && $meta['payment_method'] === 'mixed') {
                $details[] = 'Efectivo: Bs. ' . number_format((float) $meta['cash_paid_amount'], 2);
            }
            if (isset($meta['qr_paid_amount']) && $meta['payment_method'] === 'mixed') {
                $details[] = 'QR: Bs. ' . number_format((float) $meta['qr_paid_amount'], 2);
            }
            if (isset($meta['change_amount']) && $meta['payment_method'] === 'cash') {
                $details[] = 'Vuelto: Bs. ' . number_format((float) $meta['change_amount'], 2);
            }
            if (isset($meta['details_count'])) {
                $details[] = 'Lineas procesadas: ' . $meta['details_count'];
            }
        } elseif ($audit->action === 'cancelled') {
            $title = 'Pedido cancelado';
        } elseif ($audit->action === 'reverted') {
            $title = 'Venta revertida';
            if (!empty($meta['reason'])) {
                $details[] = 'Motivo: ' . $meta['reason'];
            }
        }

        return [
            'title' => $title,
            'user' => $audit->user ? $audit->user->name : 'Sistema',
            'date' => $audit->created_at->format('d/m/Y H:i'),
            'details' => $details,
        ];
    }

    private function summarizeAuditItems(array $items, string $prefix, array $productNameMap): array
    {
        return collect($items)
            ->map(function ($item) use ($prefix, $productNameMap) {
                $notes = trim((string) ($item['notes'] ?? ''));
                $productId = (int) ($item['product_id'] ?? 0);
                $productName = $productNameMap[$productId] ?? ('Producto #' . ($item['product_id'] ?? '-'));
                $line = $prefix . ': ' . $productName . ' x' . ((int) ($item['quantity'] ?? 0));
                $serviceType = ($item['service_type'] ?? 'dine_in') === 'takeaway' ? 'Para llevar' : 'En mesa';
                $line .= ' [' . $serviceType . ']';

                if ($notes !== '') {
                    $line .= ' [' . $notes . ']';
                }

                return $line;
            })
            ->values()
            ->all();
    }

    private function buildItemDiffDetails(array $beforeItems, array $afterItems, array $productNameMap): array
    {
        $before = $this->groupAuditItemsForDiff($beforeItems);
        $after = $this->groupAuditItemsForDiff($afterItems);
        $keys = $before->keys()->merge($after->keys())->unique()->values();
        $details = [];

        foreach ($keys as $key) {
            $beforeItem = $before->get($key);
            $afterItem = $after->get($key);
            $beforeQty = (int) ($beforeItem['quantity'] ?? 0);
            $afterQty = (int) ($afterItem['quantity'] ?? 0);

            if ($beforeQty === $afterQty) {
                continue;
            }

            $productId = (int) ($afterItem['product_id'] ?? $beforeItem['product_id'] ?? 0);
            $productName = $productNameMap[$productId] ?? ('Producto #' . ($productId ?: '-'));
            $notes = trim((string) ($afterItem['notes'] ?? $beforeItem['notes'] ?? ''));
            $serviceType = (($afterItem['service_type'] ?? $beforeItem['service_type'] ?? 'dine_in') === 'takeaway')
                ? 'Para llevar'
                : 'En mesa';

            if ($beforeQty === 0 && $afterQty > 0) {
                $line = 'Agregado: ' . $productName . ' x' . $afterQty;
            } elseif ($afterQty === 0 && $beforeQty > 0) {
                $line = 'Eliminado: ' . $productName . ' x' . $beforeQty;
            } else {
                $line = 'Cantidad cambiada: ' . $productName . ' de x' . $beforeQty . ' a x' . $afterQty;
            }

            $line .= ' [' . $serviceType . ']';

            if ($notes !== '') {
                $line .= ' [' . $notes . ']';
            }

            $details[] = $line;
        }

        return $details;
    }

    private function groupAuditItemsForDiff(array $items)
    {
        return collect($items)
            ->groupBy(function ($item) {
                return (int) ($item['product_id'] ?? 0)
                    . '|' . mb_strtolower(trim((string) ($item['notes'] ?? '')))
                    . '|' . ($item['service_type'] ?? 'dine_in');
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'] ?? null,
                    'notes' => $first['notes'] ?? null,
                    'service_type' => $first['service_type'] ?? 'dine_in',
                    'quantity' => collect($group)->sum(fn ($item) => (int) ($item['quantity'] ?? 0)),
                ];
            });
    }

    private function buildAuditProductNameMap(Order $order, $audits): array
    {
        $auditProductIds = $audits
            ->flatMap(function (OrderAudit $audit) {
                $meta = $audit->meta ?? [];

                return collect([
                    ...collect($meta['items'] ?? [])->pluck('product_id')->all(),
                    ...collect(data_get($meta, 'before.items', []))->pluck('product_id')->all(),
                    ...collect(data_get($meta, 'after.items', []))->pluck('product_id')->all(),
                ]);
            })
            ->filter()
            ->map(fn ($id) => (int) $id);

        $orderProductIds = $order->details->pluck('product_id')->map(fn ($id) => (int) $id);

        return Product::whereIn('id', $auditProductIds->merge($orderProductIds)->unique()->values())
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getSelectableTables(?Order $currentOrder = null)
    {
        $currentTableId = $currentOrder?->table_id;
        $currentOrderId = $currentOrder?->id;

        $query = DiningTable::query();

        if ($currentTableId) {
            $query->where('id', $currentTableId)
                ->orWhere(function ($availableQuery) use ($currentOrderId) {
                    $availableQuery->where('active', true)
                        ->whereDoesntHave('activeOrders', function ($orderQuery) use ($currentOrderId) {
                            if ($currentOrderId) {
                                $orderQuery->where('orders.id', '!=', $currentOrderId);
                            }
                        });
                });
        } else {
            $query->where('active', true)
                ->whereDoesntHave('activeOrders');
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name', 'active', 'reservation_name', 'reservation_at', 'zone']);
    }

    private function getTableBoard()
    {
        return DiningTable::query()
            ->withCount('activeOrders')
            ->orderByRaw('COALESCE(zone, "")')
            ->orderBy('name')
            ->get(['id', 'name', 'zone', 'active', 'reservation_name', 'reservation_at']);
    }

    private function resolveTableForOrder(int $tableId, ?Order $currentOrder = null): DiningTable
    {
        $table = DiningTable::findOrFail($tableId);

        if ($currentOrder && $currentOrder->table_id === $table->id) {
            return $table;
        }

        if (!$table->active) {
            throw new \RuntimeException('La mesa seleccionada está cerrada.');
        }

        $hasActiveOrder = $table->activeOrders()
            ->when($currentOrder, fn ($query) => $query->where('orders.id', '!=', $currentOrder->id))
            ->exists();

        if ($hasActiveOrder) {
            throw new \RuntimeException('La mesa seleccionada ya tiene un pedido activo.');
        }

        return $table;
    }

    private function normalizeOrderItems($items)
    {
        return collect($items)
            ->map(function ($item) {
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    return null;
                }

                $serviceType = $item['service_type'] ?? 'dine_in';

                return [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'quantity' => $quantity,
                    'unit_price' => isset($item['unit_price']) ? (float) $item['unit_price'] : null,
                    'notes' => $item['notes'] ?? null,
                    'service_type' => $serviceType,
                ];
            })
            ->filter()
            ->groupBy(function ($item) {
                return ($item['service_type'] ?? 'dine_in')
                    . '|' . (int) ($item['product_id'] ?? 0)
                    . '|' . mb_strtolower(trim((string) ($item['notes'] ?? '')));
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'],
                    'quantity' => collect($group)->sum('quantity'),
                    'unit_price' => $first['unit_price'] ?? null,
                    'notes' => $first['notes'],
                    'service_type' => $first['service_type'],
                ];
            })
            ->values();
    }

    private function replaceOrderDetails(Order $order, $items, bool $useCustomPrice = false): void
    {
        $order->details()->delete();

        foreach (collect($items) as $item) {
            $product = Product::findOrFail($item['product_id']);
            $unitPrice = $useCustomPrice && isset($item['unit_price']) && $item['unit_price'] !== null
                ? (float) $item['unit_price']
                : (float) $product->price;

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'notes' => $item['notes'] ?? null,
                'service_type' => $item['service_type'] ?? 'dine_in',
            ]);
        }
    }

    private function inferServiceMode($items): string
    {
        return (new Order())->inferServiceModeFromItems($items);
    }

    private function mapKitchenFoodItems(Collection $items, Collection $products): Collection
    {
        return $items->map(function ($item) use ($products) {
            $productId = (int) ($item['product_id'] ?? 0);
            $product = $products->get($productId);

            if (!$product || $product->isBeverage()) {
                return null;
            }

            return (object) [
                'product' => $product,
                'quantity' => (int) ($item['quantity'] ?? 0),
                'notes' => $item['notes'] ?? null,
                'service_type' => $item['service_type'] ?? 'dine_in',
                'service_type_label' => ($item['service_type'] ?? 'dine_in') === 'takeaway' ? 'Para llevar' : 'En mesa',
            ];
        })->filter()->values();
    }

    private function isDeliveryWaiter(User $waiter): bool
    {
        return $waiter->isDeliveryWaiter();
    }

    public function printReceipt($id)
    {
        $order = Order::with(['user', 'cashier', 'details.product'])
            ->findOrFail($id);

        return view('cashier.receipt', compact('order'));
    }

    public function downloadReceipt($id)
    {
        $order = Order::with(['user', 'cashier', 'details.product'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('cashier.receipt-pdf', compact('order'));
        
        return $pdf->download('boleta-' . $order->id . '.pdf');
    }

    public function sales(Request $request)
    {
        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier']);

        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $orders = $query->latest('completed_at')->paginate(20);
        $totalSales = $query->sum('total');

        $audits = OrderAudit::with(['order', 'user'])->latest()->take(20)->get();

        return view('cashier.sales', compact('orders', 'totalSales', 'audits'));
    }

    public function salesPrint(Request $request)
    {
        $type = $request->get('type', 'day');
        if (!in_array($type, ['day', 'month'], true)) {
            $type = 'day';
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if (!$dateFrom && !$dateTo) {
            if ($type === 'day') {
                $dateFrom = now()->toDateString();
                $dateTo = now()->toDateString();
            } else {
                $dateFrom = now()->startOfMonth()->toDateString();
                $dateTo = now()->endOfMonth()->toDateString();
            }
        }

        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details'])
            ->orderBy('completed_at');

        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }

        $orders = $query->get();
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();

        $grouped = $orders->groupBy(function ($order) use ($type) {
            $date = $order->completed_at ?? $order->created_at;
            return $type === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');
        });

        return view('cashier.sales-print', [
            'orders' => $orders,
            'grouped' => $grouped,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
