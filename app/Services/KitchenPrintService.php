<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
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

    public function buildAddedPrintPayload(Order $order): array
    {
        $products = Product::with('category')->get()->keyBy('id');
        [$foodItems, $printedAt] = $this->buildLatestAddedItems($order, $products);

        return [
            'foodItems' => $foodItems,
            'printLabel' => 'ULTIMOS AGREGADOS',
            'printedAt' => $printedAt,
            'scope' => 'added',
            'printReference' => $order->display_number . '-' . $this->latestAddedSequence($order),
        ];
    }

    public function hasPrintableAddedItems(Order $order): bool
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
