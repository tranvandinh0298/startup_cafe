<?php

namespace Tests\Unit;

use App\Services\InventoryConsumeService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryConsumeTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_inventory_is_consumed_pass()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 400], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 2, // need 100
        ], ORDER_STATUS_PAID);

        $service = app(InventoryConsumeService::class);
        $service->consumePaidOrder($order->id, 1);

        $this->assertDatabaseCount('inventory_actions', 1);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 300,
        ]);
    }

    public function test_inventory_is_consumed_with_2_batches()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 80], $coffeeLot, $user);
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 1000], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 2, // need 100
        ], ORDER_STATUS_PAID);

        $service = app(InventoryConsumeService::class);
        $service->consumePaidOrder($order->id, $user->id);

        $this->assertDatabaseCount('inventory_actions', 2);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 0,
        ]);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 980,
        ]);
    }

    public function test_inventory_is_consumed_twice()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 500], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 4, // need 200
        ], ORDER_STATUS_PAID);

        $service = app(InventoryConsumeService::class);
        $service->consumePaidOrder($order->id, $user->id);

        $this->assertDatabaseCount('inventory_actions', 1);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 300,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(DOMAIN_EXCEPTION_INVENTORY_ALREADY_CONSUMED);

        $service->consumePaidOrder($order->id, $user->id);
    }

    public function test_inventory_is_consumed_when_inventory_insuffient()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 90], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 1, // need 50
        ], ORDER_STATUS_PAID);

        $service = app(InventoryConsumeService::class);

        $service->consumePaidOrder($order->id, $user->id);

        $this->assertDatabaseCount('inventory_actions', 1);
        $this->assertDatabaseHas('inventory_batches', [
            'remaining_quantity_base' => 40,
        ]);

        $order = $this->createOrder([
            $product->id => 1, // need 50
        ], ORDER_STATUS_PAID);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(DOMAIN_EXCEPTION_INVENTORY_INSUFFICIENT);

        $service->consumePaidOrder($order->id, $user->id);
    }
}
