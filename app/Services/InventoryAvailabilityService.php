<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\OrderItem;
use App\Models\ProductIngredient;

class InventoryAvailabilityService
{
    public function check(int $orderId)
    {
        $items = OrderItem::where('order_id', $orderId)->get();

        // requiredMap[ingredient_id] = required_base_units
        $requiredMap = [];

        foreach ($items as $item) {
            $recipes = ProductIngredient::where('product_id', $item->product_id)->get();

            foreach ($recipes as $r) {
                $requiredMap[$r->ingredient_id] = ($requiredMap[$r->ingredient_id] ?? 0)
                    + ($item->quantity * $r->quantity_per_unit);
            }
        }

        $shortages = [];

        foreach ($requiredMap as $ingredientId => $required) {
            $available = InventoryBatch::where('ingredient_id', $ingredientId)
                ->where('status', PACKAGE_STATUS_OPEN)
                ->where('expired_at', '>', now())
                ->sum('remaining_quantity_base');

            if ($available < $required) {
                $shortages[] = [
                    'ingredient_id' => $ingredientId,
                    'required_base_units' => $required,
                    'available_base_units' => (int)$available,
                    'missing_base_units' => (int)($required - $available),
                ];
            }
        }

        return [
            'ok' => count($shortages) === 0,
            'shortages' => $shortages,
        ];
    }
}
