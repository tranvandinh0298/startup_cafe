<?php

namespace App\Services;

use App\Models\InventoryAction;
use App\Models\InventoryBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductIngredient;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryConsumeService
{
    public function consumePaidOrder(int $orderId, int $userId): void
    {
        DB::transaction(function () use ($orderId, $userId) {

            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->inventory_consumed_at !== null) {
                // idempotent
                throw new DomainException(DOMAIN_EXCEPTION_INVENTORY_ALREADY_CONSUMED);
            }

            if ($order->status !== ORDER_STATUS_PAID) {
                throw new DomainException(DOMAIN_EXCEPTION_ORDER_NOT_PAID);
            }

            $orderItems = OrderItem::where('order_id', $orderId)->get();

            // preload recipes
            $recipesByProduct = ProductIngredient::whereIn(
                'product_id',
                $orderItems->pluck('product_id')->unique()
            )->get()->groupBy('product_id');

            // lock all related batches
            $ingredientIds = $recipesByProduct->flatten()->pluck('ingredient_id')->unique();

            $batches = InventoryBatch::whereIn('ingredient_id', $ingredientIds)
                ->where('status', PACKAGE_STATUS_OPEN)
                ->where('expired_at', '>', now())
                ->orderBy('expired_at')
                ->orderBy('opened_at')
                ->lockForUpdate()
                ->get()
                ->groupBy('ingredient_id');

            foreach ($orderItems as $item) {
                $recipes = $recipesByProduct->get($item->product_id, collect());

                foreach ($recipes as $recipe) {
                    $ingredientId = $recipe->ingredient_id;
                    $need = $recipe->quantity_per_unit * $item->quantity;

                    $list = $batches->get($ingredientId, collect());

                    foreach ($list as $batch) {
                        if ($need <= 0) break;

                        $have = (int)$batch->remaining_quantity_base;
                        if ($have <= 0) continue;

                        $take = min($have, $need);

                        $batch->remaining_quantity_base = $have - $take;
                        if ($batch->remaining_quantity_base === 0) {
                            $batch->status = PACKAGE_STATUS_USED_UP;
                        }
                        $batch->save();

                        InventoryAction::create([
                            'action_type' => INVENTORY_ACTION_TYPE_CONSUME,
                            'order_id' => $orderId,
                            'order_item_id' => $item->id,
                            'inventory_batch_id' => $batch->id,
                            'inventory_lot_id' => $batch->inventory_lot_id,
                            'ingredient_id' => $ingredientId,
                            'quantity_base_units' => -$take,
                            'user_id' => $userId,
                        ]);

                        $need -= $take;
                    }

                    if ($need > 0) {
                        throw new DomainException(DOMAIN_EXCEPTION_INVENTORY_INSUFFICIENT);
                    }
                }
            }

            $order->inventory_consumed_at = now();
            $order->status = ORDER_STATUS_COMPLETED;
            $order->save();
        });
    }
}
