<?php

namespace Tests\Unit;

use App\Services\InventoryConsumeService;
use App\Services\InventoryRollbackService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryRollbackTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_rollback_restores_inventory()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $coffeeBatch = $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 200], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 2, // need 100
        ], ORDER_STATUS_PAID);

        app(InventoryConsumeService::class)
            ->consumePaidOrder($order->id, $user->id);

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $coffeeBatch->id,
            'remaining_quantity_base' => 100,
        ]);

        $this->assertDatabaseHas('inventory_actions', [
            'action_type' => INVENTORY_ACTION_TYPE_CONSUME,
        ]);

        app(InventoryRollbackService::class)
            ->rollbackOrder($order->id, $user->id);

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $coffeeBatch->id,
            'remaining_quantity_base' => 200,
        ]);

        $this->assertDatabaseHas('inventory_actions', [
            'action_type' => INVENTORY_ACTION_TYPE_ROLLBACK,
        ]);
    }

    public function test_rollback_non_consumed_order()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $coffeeBatch = $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 200], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 2, // need 100
        ], ORDER_STATUS_PAID);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(DOMAIN_EXCEPTION_ORDER_NOT_CONSUMED);

        app(InventoryRollbackService::class)
            ->rollbackOrder($order->id, $user->id);
    }

    public function test_rollback_order_twice()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 0);
        $coffeeBatch = $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 200], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $order = $this->createOrder([
            $product->id => 2, // need 100
        ], ORDER_STATUS_PAID);

        app(InventoryConsumeService::class)
            ->consumePaidOrder($order->id, $user->id);

        app(InventoryRollbackService::class)
            ->rollbackOrder($order->id, $user->id);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(DOMAIN_EXCEPTION_ORDER_NOT_ROLLBACKABLE);

        app(InventoryRollbackService::class)
            ->rollbackOrder($order->id, $user->id);
    }
}
