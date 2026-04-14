<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Services\KitchenPrintService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaiterController extends Controller
{
    public function dashboard(OrderWorkflowService $workflow)
    {
        $categories = Category::where('active', true)
            ->with(['activeProducts' => function($query) {
                $query->orderBy('name');
            }])
            ->get();

        $availableTables = $workflow->getSelectableTables();
        $tableBoard = $workflow->getTableBoard();

        $pendingOrders = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with('details.product')
            ->latest()
            ->get();

        return view('waiter.dashboard', compact('categories', 'pendingOrders', 'availableTables', 'tableBoard'));
    }

    public function tableBoardStatus(OrderWorkflowService $workflow)
    {
        $tableBoard = $workflow->getTableBoard();
        $selectableTableIds = $workflow->getSelectableTables()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return response()->json([
            'has_selectable_tables' => !empty($selectableTableIds),
            'tables' => $tableBoard->map(function (DiningTable $table) use ($selectableTableIds) {
                return [
                    'id' => (int) $table->id,
                    'name' => $table->merged_display_name,
                    'status' => $table->ui_status,
                    'selectable' => in_array((int) $table->id, $selectableTableIds, true),
                    'combined_capacity' => $table->combined_capacity,
                    'zone' => $table->zone,
                    'has_merged_children' => $table->hasMergedChildren(),
                    'merged_members' => $table->merged_members->pluck('name')->values()->all(),
                    'reservation_name' => $table->isReserved() ? $table->reservation_name : null,
                    'reservation_at_label' => $table->isReserved() && $table->reservation_at
                        ? $table->reservation_at->format('d/m H:i')
                        : null,
                    'group_reservation_summary' => $table->group_reservation_summary,
                ];
            })->values(),
        ]);
    }

    public function createOrder(Request $request, OrderWorkflowService $workflow)
    {
        $request->validate([
            'table_id' => 'required|integer|exists:tables,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'items.*.service_type' => 'nullable|in:dine_in,takeaway',
        ]);

        try {
            $order = $workflow->createOrder(
                Auth::user(),
                collect($request->items),
                $request->integer('table_id'),
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'order_id' => $order->id,
                'display_number' => $order->display_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear pedido (mesero)', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pedido: ' . $e->getMessage(),
            ], $e instanceof \RuntimeException ? 422 : 500);
        }
    }

    public function myOrders(Request $request, KitchenPrintService $kitchenPrint)
    {
        $currentView = $request->query('view', 'pending');
        if (!in_array($currentView, ['pending', 'completed', 'cancelled'], true)) {
            $currentView = 'pending';
        }

        $baseQuery = Order::where('user_id', Auth::id());

        $ordersQuery = (clone $baseQuery)
            ->with(['details.product', 'cashier', 'diningTable', 'audits.user'])
            ->latest();

        if ($currentView === 'completed') {
            $ordersQuery->where('status', 'completed');
        } elseif ($currentView === 'cancelled') {
            $ordersQuery->where('status', 'cancelled');
        } else {
            $ordersQuery->whereIn('status', ['pending', 'processing']);
        }

        $orders = $ordersQuery->paginate(20)->withQueryString();

        $orders->getCollection()->transform(function (Order $order) use ($kitchenPrint) {
            $order->can_print_added = $order->status === 'pending' && $kitchenPrint->hasPrintableAddedItems($order, 'mesero');
            return $order;
        });

        $pendingCount = (clone $baseQuery)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $completedCount = (clone $baseQuery)
            ->where('status', 'completed')
            ->count();

        $cancelledCount = (clone $baseQuery)
            ->where('status', 'cancelled')
            ->count();

        $products = Product::where('active', true)
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock', 'category_id', 'image']);

        return view('waiter.orders', compact('orders', 'products', 'currentView', 'pendingCount', 'completedCount', 'cancelledCount'));
    }

    public function orderDetails($id, OrderWorkflowService $workflow)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['details.product', 'cashier', 'diningTable'])
            ->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'display_number' => $order->display_number,
            'table_id' => $order->table_id,
            'table_number' => $order->table_label,
            'service_mode' => $order->service_mode,
            'service_mode_label' => $order->service_mode_label,
            'status' => $order->status,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'subtotal' => number_format($order->subtotal, 2, '.', ''),
            'total' => number_format($order->total, 2, '.', ''),
            'can_edit' => $order->status === 'pending',
            'cashier' => $order->cashier ? $order->cashier->name : null,
            'available_tables' => $workflow->getSelectableTables($order)->map(fn ($table) => [
                'id' => $table->id,
                'name' => $table->merged_display_name,
            ])->values(),
            'details' => $order->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product ? $detail->product->name : 'Producto',
                    'quantity' => (int) $detail->quantity,
                    'unit_price' => number_format($detail->unit_price, 2, '.', ''),
                    'subtotal' => number_format($detail->subtotal, 2, '.', ''),
                    'notes' => $detail->notes,
                    'service_type' => $detail->service_type,
                    'service_type_label' => $detail->service_type_label,
                ];
            }),
        ]);
    }

    public function printOrder(KitchenPrintService $kitchenPrint, $id, $scope = 'main')
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['user', 'details.product.category', 'audits.user'])
            ->findOrFail($id);

        $scope = $scope === 'added' ? 'added' : 'main';
        $payload = $scope === 'added'
            ? $kitchenPrint->buildAddedPrintPayload($order, 'mesero')
            : $kitchenPrint->buildMainPrintPayload($order);

        if ($scope === 'added') {
            $kitchenPrint->markAddedItemsPrinted($order, Auth::id(), 'mesero', $payload['foodItems']);
        }

        return view('waiter.print-order', array_merge([
            'order' => $order,
            'autoCloseAfterPrint' => false,
            'returnUrl' => route('waiter.orders'),
        ], $payload));
    }

    public function updateOrderItems(Request $request, $id, OrderWorkflowService $workflow)
    {
        $request->validate([
            'table_id' => 'required|integer|exists:tables,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string',
            'items.*.service_type' => 'nullable|in:dine_in,takeaway',
        ]);

        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with('details')
            ->findOrFail($id);

        try {
            $workflow->updateOrder(
                $order,
                Auth::user(),
                collect($request->items),
                $request->integer('table_id'),
                Auth::id()
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar pedido (mesero)', ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pedido: ' . $e->getMessage(),
            ], $e instanceof \RuntimeException ? 422 : 500);
        }
    }

    public function cancelOrder($id, OrderWorkflowService $workflow)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $workflow->cancelPendingOrder($order, Auth::id());

        return redirect()->back()->with('success', 'Pedido cancelado exitosamente');
    }

    private function buildCreatedItems(Order $order, Collection $products): Collection
    {
        $items = $order->details->map(fn ($detail) => [
            'product_id' => $detail->product_id,
            'quantity' => $detail->quantity,
            'notes' => $detail->notes,
            'service_type' => $detail->service_type,
        ]);

        return $this->mapFoodItems($items, $products);
    }

    private function buildLatestAddedItems(Order $order, Collection $products): array
    {
        $latestUpdate = $order->audits
            ->where('action', 'updated')
            ->sortByDesc('created_at')
            ->first();

        if (!$latestUpdate) {
            return [collect(), $order->updated_at ?? $order->created_at];
        }

        $before = $this->groupAuditItemsBySignature($latestUpdate['meta']['before']['items'] ?? []);
        $after = $this->groupAuditItemsBySignature($latestUpdate['meta']['after']['items'] ?? []);

        $addedItems = $after->map(function ($item, $signature) use ($before) {
            $afterQty = (int) ($item['quantity'] ?? 0);
            $beforeQty = (int) ($before->get($signature)['quantity'] ?? 0);
            $delta = $afterQty - $beforeQty;

            if ($delta <= 0) {
                return null;
            }

            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'quantity' => $delta,
                'notes' => $item['notes'] ?? null,
                'service_type' => $item['service_type'] ?? 'dine_in',
            ];
        })->values();

        return [$this->mapFoodItems($addedItems, $products), $latestUpdate->created_at];
    }

    private function groupAuditItemsBySignature(array $items): Collection
    {
        return collect($items)
            ->groupBy(fn ($item) => $this->makeAuditItemSignature($item))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => (int) ($first['product_id'] ?? 0),
                    'quantity' => $group->sum(fn ($row) => (int) ($row['quantity'] ?? 0)),
                    'notes' => $first['notes'] ?? null,
                    'service_type' => $first['service_type'] ?? 'dine_in',
                ];
            });
    }

    private function makeAuditItemSignature(array $item): string
    {
        return sprintf(
            '%s|%s|%s',
            (int) ($item['product_id'] ?? 0),
            mb_strtolower(trim((string) ($item['notes'] ?? ''))),
            $item['service_type'] ?? 'dine_in'
        );
    }

    private function getSelectableTables(?Order $currentOrder = null): Collection
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

    private function getTableBoard(): Collection
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

    private function normalizeOrderItems(Collection $items): Collection
    {
        return $items
            ->map(function ($item) {
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    return null;
                }

                $serviceType = $item['service_type'] ?? 'dine_in';

                return [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'quantity' => $quantity,
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
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'],
                    'quantity' => $group->sum('quantity'),
                    'notes' => $first['notes'],
                    'service_type' => $first['service_type'],
                ];
            })
            ->values();
    }

    private function replaceOrderDetails(Order $order, Collection $items): void
    {
        $order->details()->delete();

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'notes' => $item['notes'] ?? null,
                'service_type' => $item['service_type'] ?? 'dine_in',
            ]);
        }
    }

    private function inferServiceMode(Collection $items): string
    {
        return (new Order())->inferServiceModeFromItems($items);
    }

    private function mapFoodItems(Collection $items, Collection $products): Collection
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

    private function hasPrintableAddedItems(Order $order): bool
    {
        $latestUpdate = $order->audits
            ->where('action', 'updated')
            ->sortByDesc('created_at')
            ->first();

        if (!$latestUpdate) {
            return false;
        }

        $before = $this->groupAuditItemsBySignature($latestUpdate['meta']['before']['items'] ?? []);
        $after = $this->groupAuditItemsBySignature($latestUpdate['meta']['after']['items'] ?? []);

        $foodProductIds = $this->foodProductIds();

        foreach ($after as $signature => $item) {
            if (!in_array((int) ($item['product_id'] ?? 0), $foodProductIds, true)) {
                continue;
            }

            $afterQty = (int) ($item['quantity'] ?? 0);
            $beforeQty = (int) ($before->get($signature)['quantity'] ?? 0);

            if ($afterQty - $beforeQty > 0) {
                return true;
            }
        }

        return false;
    }

    private function latestAddedSequence(Order $order): int
    {
        $sequence = 0;
        $foodProductIds = $this->foodProductIds();

        foreach ($order->audits->where('action', 'updated')->sortBy('created_at') as $audit) {
            $before = $this->groupAuditItemsBySignature($audit['meta']['before']['items'] ?? []);
            $after = $this->groupAuditItemsBySignature($audit['meta']['after']['items'] ?? []);

            foreach ($after as $signature => $item) {
                if (!in_array((int) ($item['product_id'] ?? 0), $foodProductIds, true)) {
                    continue;
                }

                $afterQty = (int) ($item['quantity'] ?? 0);
                $beforeQty = (int) ($before->get($signature)['quantity'] ?? 0);

                if ($afterQty - $beforeQty > 0) {
                    $sequence++;
                    break;
                }
            }
        }

        return max(1, $sequence);
    }

    private function foodProductIds(): array
    {
        return Product::query()
            ->whereHas('category', function ($query) {
                $query->where('code', '!=', Category::CODE_BEVERAGES);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
