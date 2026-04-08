<?php

namespace App\Services;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderAudit;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    public function getSelectableTables(?Order $currentOrder = null): Collection
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

    public function getTableBoard(): Collection
    {
        return DiningTable::query()
            ->withCount('activeOrders')
            ->orderByRaw('COALESCE(zone, "")')
            ->orderBy('name')
            ->get(['id', 'name', 'zone', 'active', 'reservation_name', 'reservation_at']);
    }

    public function createOrder(User $waiter, Collection $items, ?int $tableId, int $auditUserId, bool $useCustomPrice = false): Order
    {
        return DB::transaction(function () use ($waiter, $items, $tableId, $auditUserId, $useCustomPrice) {
            $resolvedItems = $this->normalizeOrderItems($items, $waiter->isDeliveryWaiter(), $useCustomPrice);
            $table = $this->resolveTableForWaiter($waiter, $tableId);

            $order = Order::create([
                'user_id' => $waiter->id,
                'table_id' => $table?->id,
                'table_number' => $table?->name ?? 'Delivery',
                'service_mode' => $this->inferServiceMode($resolvedItems),
                'subtotal' => 0,
                'total' => 0,
                'status' => 'pending',
            ]);

            $this->replaceOrderDetails($order, $resolvedItems, $useCustomPrice);
            $order->load('details');
            $order->service_mode = $this->inferServiceMode($resolvedItems);
            $order->calculateTotal();

            $this->logAudit($order->id, $auditUserId, 'created', [
                'table_id' => $order->table_id,
                'table_number' => $order->table_number,
                'waiter_id' => $order->user_id,
                'service_mode' => $order->service_mode,
                'items' => $resolvedItems->values()->all(),
            ]);

            return $order;
        });
    }

    public function updateOrder(Order $order, User $waiter, Collection $items, ?int $tableId, int $auditUserId, bool $useCustomPrice = false): Order
    {
        return DB::transaction(function () use ($order, $waiter, $items, $tableId, $auditUserId, $useCustomPrice) {
            $resolvedItems = $this->normalizeOrderItems($items, $waiter->isDeliveryWaiter(), $useCustomPrice);
            $table = $this->resolveTableForWaiter($waiter, $tableId, $order);

            $before = [
                'table_id' => $order->table_id,
                'table_number' => $order->table_number,
                'waiter_id' => $order->user_id,
                'service_mode' => $order->service_mode,
                'items' => $order->details->map(fn ($d) => [
                    'product_id' => $d->product_id,
                    'quantity' => $d->quantity,
                    'unit_price' => $d->unit_price,
                    'notes' => $d->notes,
                    'service_type' => $d->service_type,
                ])->values(),
            ];

            $order->user_id = $waiter->id;
            $order->table_id = $table?->id;
            $order->table_number = $table?->name ?? 'Delivery';
            $order->service_mode = $this->inferServiceMode($resolvedItems);
            $order->save();

            $this->replaceOrderDetails($order, $resolvedItems, $useCustomPrice);
            $order->load('details');
            $order->service_mode = $this->inferServiceMode($resolvedItems);
            $order->calculateTotal();

            $after = [
                'table_id' => $order->table_id,
                'table_number' => $order->table_number,
                'waiter_id' => $order->user_id,
                'service_mode' => $order->service_mode,
                'items' => $order->details->map(fn ($d) => [
                    'product_id' => $d->product_id,
                    'quantity' => $d->quantity,
                    'unit_price' => $d->unit_price,
                    'notes' => $d->notes,
                    'service_type' => $d->service_type,
                ])->values(),
            ];

            $this->logAudit($order->id, $auditUserId, 'updated', [
                'before' => $before,
                'after' => $after,
            ]);

            return $order;
        });
    }

    public function normalizeOrderItems(Collection $items, bool $forceTakeaway = false, bool $allowCustomPrice = false): Collection
    {
        return $items
            ->map(function ($item) use ($forceTakeaway, $allowCustomPrice) {
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    return null;
                }

                $serviceType = $forceTakeaway
                    ? 'takeaway'
                    : ($item['service_type'] ?? 'dine_in');

                return [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'quantity' => $quantity,
                    'unit_price' => $allowCustomPrice && array_key_exists('unit_price', (array) $item)
                        ? (float) $item['unit_price']
                        : null,
                    'notes' => $item['notes'] ?? null,
                    'service_type' => $serviceType,
                ];
            })
            ->filter()
            ->groupBy(function ($item) use ($allowCustomPrice) {
                $signature = ($item['service_type'] ?? 'dine_in')
                    . '|' . (int) ($item['product_id'] ?? 0)
                    . '|' . mb_strtolower(trim((string) ($item['notes'] ?? '')));

                if ($allowCustomPrice) {
                    $signature .= '|' . number_format((float) ($item['unit_price'] ?? 0), 2, '.', '');
                }

                return $signature;
            })
            ->map(function (Collection $group) use ($allowCustomPrice) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'],
                    'quantity' => $group->sum('quantity'),
                    'unit_price' => $allowCustomPrice ? ($first['unit_price'] ?? null) : null,
                    'notes' => $first['notes'],
                    'service_type' => $first['service_type'],
                ];
            })
            ->values();
    }

    public function inferServiceMode(Collection $items): string
    {
        return (new Order())->inferServiceModeFromItems($items);
    }

    public function resolveTableForWaiter(User $waiter, ?int $tableId, ?Order $currentOrder = null): ?DiningTable
    {
        if ($waiter->isDeliveryWaiter()) {
            return null;
        }

        if (!$tableId) {
            throw new \RuntimeException('Debes seleccionar una mesa para este mesero.');
        }

        return $this->resolveTableForOrder($tableId, $currentOrder);
    }

    public function resolveTableForOrder(int $tableId, ?Order $currentOrder = null): DiningTable
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

    private function replaceOrderDetails(Order $order, Collection $items, bool $useCustomPrice = false): void
    {
        $order->details()->delete();

        $products = Product::query()
            ->whereIn('id', $items->pluck('product_id')->unique()->values())
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $products->get($item['product_id']) ?? Product::findOrFail($item['product_id']);
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

    private function logAudit(int $orderId, int $userId, string $action, array $meta): void
    {
        OrderAudit::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'action' => $action,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
