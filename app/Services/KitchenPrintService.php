<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderAudit;
use App\Models\Product;
use Illuminate\Support\Collection;

class KitchenPrintService
{
    public function buildMainPrintPayload(Order $order): array
    {
        $products = Product::with('category')->get()->keyBy('id');

        return [
            'foodItems' => $this->mapFoodItems(
                $order->details->map(fn ($detail) => [
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity,
                    'notes' => $detail->notes,
                    'service_type' => $detail->service_type,
                ]),
                $products
            ),
            'printLabel' => 'PEDIDO PRINCIPAL',
            'printedAt' => $order->created_at,
            'scope' => 'main',
            'printReference' => $order->display_number,
        ];
    }

    public function buildAddedPrintPayload(Order $order, ?string $actorRole = null): array
    {
        return $this->buildScopedAddedPrintPayload($order, $actorRole);
    }

    public function buildCashierAddedPrintPayload(Order $order): array
    {
        return $this->buildScopedAddedPrintPayload($order, 'cajero');
    }

    public function hasPrintableAddedItems(Order $order, ?string $actorRole = null): bool
    {
        $products = Product::with('category')->get()->keyBy('id');
        [$foodItems] = $this->buildPendingAddedItems($order, $products, $actorRole);

        return $foodItems->isNotEmpty();
    }

    public function markAddedItemsPrinted(Order $order, int $userId, string $actorRole, Collection $foodItems): void
    {
        if ($foodItems->isEmpty()) {
            return;
        }

        OrderAudit::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'action' => 'kitchen_added_printed',
            'meta' => [
                'actor_role' => $actorRole,
                'items' => $foodItems->map(function ($item) {
                    return [
                        'product_id' => (int) ($item->product?->id ?? 0),
                        'quantity' => (int) ($item->quantity ?? 0),
                        'notes' => $item->notes ?? null,
                        'service_type' => $item->service_type ?? 'dine_in',
                    ];
                })->values()->all(),
            ],
            'created_at' => now(),
        ]);
    }

    private function buildScopedAddedPrintPayload(Order $order, ?string $actorRole = null): array
    {
        $products = Product::with('category')->get()->keyBy('id');
        [$foodItems, $printedAt] = $this->buildPendingAddedItems($order, $products, $actorRole);
        $referenceSuffix = $this->addedPrintReferenceSuffix($actorRole);

        return [
            'foodItems' => $foodItems,
            'printLabel' => 'ULTIMOS AGREGADOS',
            'printedAt' => $printedAt,
            'scope' => 'added',
            'printReference' => $order->display_number . $referenceSuffix . $this->printedAddedSequence($order, $actorRole),
        ];
    }

    private function buildPendingAddedItems(Order $order, Collection $products, ?string $actorRole = null): array
    {
        $updatedAudits = $this->updatedAuditsByRole($order, $actorRole);
        $lastPrintedAudit = $this->latestPrintedAddedAudit($order, $actorRole);

        if ($lastPrintedAudit) {
            $updatedAudits = $updatedAudits
                ->filter(fn ($audit) => $audit->created_at->gt($lastPrintedAudit->created_at))
                ->values();
        }

        if ($updatedAudits->isEmpty()) {
            return [collect(), $order->updated_at ?? $order->created_at];
        }

        $aggregatedItems = collect();
        $printedAt = $updatedAudits->last()->created_at;

        foreach ($updatedAudits as $audit) {
            $before = $this->groupAuditItemsBySignature($audit['meta']['before']['items'] ?? []);
            $after = $this->groupAuditItemsBySignature($audit['meta']['after']['items'] ?? []);
            $signatures = $before->keys()->merge($after->keys())->unique()->values();

            foreach ($signatures as $signature) {
                $beforeItem = $before->get($signature);
                $afterItem = $after->get($signature);
                $delta = (int) ($afterItem['quantity'] ?? 0) - (int) ($beforeItem['quantity'] ?? 0);

                if ($delta === 0) {
                    continue;
                }

                $sourceItem = $afterItem ?? $beforeItem;
                $currentItem = $aggregatedItems->get($signature, [
                    'product_id' => (int) ($sourceItem['product_id'] ?? 0),
                    'quantity' => 0,
                    'notes' => $sourceItem['notes'] ?? null,
                    'service_type' => $sourceItem['service_type'] ?? 'dine_in',
                ]);

                $currentItem['quantity'] += $delta;

                if ($currentItem['quantity'] <= 0) {
                    $aggregatedItems->forget($signature);
                    continue;
                }

                $aggregatedItems->put($signature, $currentItem);
            }
        }

        return [$this->mapFoodItems($aggregatedItems->values(), $products), $printedAt];
    }

    private function mapFoodItems(Collection $items, Collection $products): Collection
    {
        return $items->map(function ($item) use ($products) {
            $productId = (int) ($item['product_id'] ?? 0);
            /** @var Product|null $product */
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

    private function groupAuditItemsBySignature(array $items): Collection
    {
        return collect($items)
            ->groupBy(fn ($item) => sprintf(
                '%s|%s|%s',
                (int) ($item['product_id'] ?? 0),
                mb_strtolower(trim((string) ($item['notes'] ?? ''))),
                $item['service_type'] ?? 'dine_in'
            ))
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

    private function printedAddedSequence(Order $order, ?string $actorRole = null): int
    {
        $order->loadMissing('audits.user');

        $printedCount = $order->audits
            ->filter(function ($audit) use ($actorRole) {
                return $audit->action === 'kitchen_added_printed'
                    && $this->auditMatchesRole($audit, $actorRole);
            })
            ->count();

        return $printedCount + 1;
    }

    private function latestPrintedAddedAudit(Order $order, ?string $actorRole = null): ?OrderAudit
    {
        $order->loadMissing('audits.user');

        return $order->audits
            ->filter(function ($audit) use ($actorRole) {
                return $audit->action === 'kitchen_added_printed'
                    && $this->auditMatchesRole($audit, $actorRole);
            })
            ->sortByDesc('created_at')
            ->first();
    }

    private function updatedAuditsByRole(Order $order, ?string $actorRole = null): Collection
    {
        $order->loadMissing('audits.user');

        return $order->audits
            ->filter(function ($audit) use ($actorRole) {
                return $audit->action === 'updated'
                    && $this->auditMatchesRole($audit, $actorRole);
            })
            ->sortBy('created_at')
            ->values();
    }

    private function auditMatchesRole(OrderAudit $audit, ?string $actorRole = null): bool
    {
        if ($actorRole === null) {
            return true;
        }

        $metaRole = mb_strtolower((string) data_get($audit->meta, 'actor_role', ''));
        $userRole = mb_strtolower((string) ($audit->user?->role ?? ''));
        $normalizedRole = mb_strtolower($actorRole);

        return $metaRole === $normalizedRole || $userRole === $normalizedRole;
    }

    private function addedPrintReferenceSuffix(?string $actorRole = null): string
    {
        return match (mb_strtolower((string) $actorRole)) {
            'cajero' => '-C',
            'mesero' => '-M',
            default => '-',
        };
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
