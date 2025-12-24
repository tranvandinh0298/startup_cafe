<?php

namespace App\Services;

use App\Models\InventoryAction;
use App\Models\InventoryBatch;
use App\Models\Order;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryRollbackService
{
    public function rollbackOrder($orderId, $userId)
    {
        DB::transaction(function () use ($orderId, $userId) {
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            if ($order->inventory_consumed_at === null) {
                throw new DomainException('ORDER_NOT_CONSUMED');
            }

            if (!in_array($order->status, [ORDER_STATUS_PAID, ORDER_STATUS_COMPLETED])) {
                throw new DomainException('ORDER_NOT_ROLLBACKABLE');
            }

            $consumeActions = InventoryAction::where('order_id', $orderId)
                ->where('action_type', INVENTORY_ACTION_TYPE_CONSUME)
                ->lockForUpdate()
                ->get();

            foreach ($consumeActions as $action) {
                $batch = InventoryBatch::where('id', $action->ineventory_batch_id)
                    ->lockForUpdate()
                    ->first();

                if (!$batch) {
                    throw new DomainException('BATCH_NOT_FOUND');
                }

                $restore = abs($action->quantity_base_units);

                $batch->remaining_quantity_base += $restore;

                if ($batch->status === PACKAGE_STATUS_USED_UP && $batch->remaining_quantity_base > 0) {
                    $batch->status = PACKAGE_STATUS_OPEN;
                }

                $batch->save();

                InventoryAction::create([
                    'action_type' => INVENTORY_ACTION_TYPE_ROLLBACK,
                    'reference_action_id' => $action->id,
                    'order_id' => $orderId,
                    'order_item_id' => $action->order_item_id,
                    'ineventory_batch_id' => $batch->id,
                    'inventory_lot_id' => $batch->inventory_lot_id,
                    'ingredient_id' => $action->ingredient_id,
                    'quantity_base_units' => $restore,
                    'user_id' => $userId,
                ]);
            }

            $order->status = ORDER_STATUS_CANCELLED;
            $order->save();
        });
    }
}
