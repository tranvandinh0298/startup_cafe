<?php

namespace Tests\Feature;

use App\Services\InventoryConsumeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryConsumeTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_inventory_is_consumed_exactly_once()
    {
        $coffee = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);
        $this->createOpenBatch($coffee, 100);

        $product = $this->createProduct('Black coffee', [
            $coffee => 50,
        ]);

        $order = $this->createOrder([
            $product => 2, // need 100
        ]);

        $order->update(['status' => 'paid']);

        $service = app(InventoryConsumeService::class);
        $service->consumePaidOrder($order->id, 1);

        // retry simulate
        $service->consumePaidOrder($order->id, 1);

        $this->assertDatabaseCount('inventory_actions', 1);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 0,
        ]);
    }
}
