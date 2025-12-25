<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\ProductIngredient;

class ProductAvailabilityService
{
    public function getAvailability(): array
    {
        // 1. Load all open batches
        $ingredientStock = InventoryBatch::where('status', PACKAGE_STATUS_OPEN)
            ->where('expired_at', '>', now())
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($batches) => $batches->sum('remaining_quantity_base'));

        // 2. Load recipes
        $recipes = ProductIngredient::all()->groupBy('product_id');

        $result = [];

        foreach ($recipes as $productId => $items) {
            $limits = [];

            foreach ($items as $r) {
                $available = $ingredientStock[$r->ingredient_id] ?? 0;

                if ($available <= 0) {
                    $limits[] = 0;
                    continue;
                }

                $limits[] = intdiv($available, $r->quantity_per_unit);
            }

            $result[$productId] = min($limits);
        }

        return $result; // product_id => available_quantity
    }
}
