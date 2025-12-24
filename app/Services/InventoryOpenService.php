<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryAction;
use App\Models\InventoryBatch;
use App\Models\InventoryLot;
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
                throw new DomainException('No inventory lot available for ingredient: ' . $ingredient->name);
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
                'action_type' => 'open',
                'inventory_lot_id' => $lot->id,
                'ineventory_batch_id' => $batch->id,
                'quantity_packages' => -1,
                'user_id' => $userId,
            ]);

            return $batch;
        });
    }
}
