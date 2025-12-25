<?php

namespace Tests\Feature;

use App\Services\InventoryConsumeService;
use App\Services\InventoryRollbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryRollbackTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_rollback_restores_inventory()
    {
        $coffee = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);
        $batch = $this->createOpenBatch($coffee, 100);

        $product = $this->createProduct('Black coffee', [
            $coffee => 50,
        ]);

        $order = $this->createOrder([
            $product => 2,
        ]);

        $order->update(['status' => 'paid']);

        app(InventoryConsumeService::class)
            ->consumePaidOrder($order->id, 1);

        app(InventoryRollbackService::class)
            ->rollbackOrder($order->id, 1);

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'remaining_quantity_base' => 100,
        ]);

        $this->assertDatabaseHas('inventory_actions', [
            'action_type' => 'rollback',
        ]);
    }
}
