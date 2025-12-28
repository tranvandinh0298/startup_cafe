<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryAction;
use App\Models\InventoryBatch;
use App\Models\InventoryLot;
use App\Models\ProductIngredient;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryOpenService
{

    public function open(int $ingredientId, int $userId): InventoryBatch
    {
        return DB::transaction(function () use ($ingredientId, $userId) {
            $ingredient = Ingredient::findOrFail($ingredientId);

            $lot = InventoryLot::where('ingredient_id', $ingredientId)
                ->where('quantity_packages', '>', 0)
                ->where('expired_at', '>', now())
                ->orderBy('expired_at')
                ->lockForUpdate()
                ->first();

            if (!$lot) {
                throw new DomainException(DOMAIN_EXCEPTION_NO_LOT_FOUND);
            }

            $lot->decrement('quantity_packages');

            $batch = InventoryBatch::create([
                'ingredient_id' => $ingredientId,
                'inventory_lot_id' => $lot->id,
                'opened_at' => now(),
                'expired_at' => now()->addHours($ingredient->shelf_life_opened_hours),
                'status' => 'open',
                'initial_quantity_base' => $ingredient->package_size,
                'remaining_quantity_base' => $ingredient->package_size,
                'opened_by' => $userId,
            ]);

            InventoryAction::create([
                'ingredient_id' => $ingredientId,
                'action_type' => PACKAGE_STATUS_OPEN,
                'inventory_lot_id' => $lot->id,
                'ineventory_batch_id' => $batch->id,
                'quantity_packages' => -1,
                'user_id' => $userId,
            ]);

            return $batch;
        });
    }

    public function suggest(): array
    {
        // Load open stock
        $openStock = InventoryBatch::where('status', PACKAGE_STATUS_OPEN)
            ->where('expired_at', '>', now())
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($b) => $b->sum('remaining_quantity_base'));

        // Load unopened packages
        $unopened = InventoryLot::where('quantity_packages', '>', 0)
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($l) => $l->sum('quantity_packages'));

        $recipes = ProductIngredient::all()->groupBy('product_id');

        $result = [];

        foreach ($recipes as $productId => $items) {
            $limits = [];

            foreach ($items as $r) {
                $available = $openStock[$r->ingredient_id] ?? 0;
                $limit = intdiv($available, $r->quantity_per_unit);
                $limits[$r->ingredient_id] = $limit;
            }

            $availableQty = min($limits);

            if ($availableQty > 3) continue; // threshold configurable

            foreach ($items as $r) {
                if ($limits[$r->ingredient_id] === $availableQty) {
                    if (($unopened[$r->ingredient_id] ?? 0) > 0) {
                        $result[] = [
                            'product_id' => $productId,
                            'ingredient_id' => $r->ingredient_id,
                            'can_open_batch' => true,
                            'unopened_packages' => $unopened[$r->ingredient_id],
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
