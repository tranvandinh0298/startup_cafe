<?php

namespace Tests\Unit;

use App\Services\OrderFeasibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class OrderFeasibilityTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_order_is_feasible_without_opening()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $this->createOpenBatch($coffeeIngredient, [], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 10], // need 500
        ]);

        $this->assertTrue($result['feasible']);
        $this->assertEquals(ORDER_FEASIBILITY_OK, $result['mode']);
    }

    public function test_order_is_feasible_with_opening()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $this->createOpenBatch($coffeeIngredient, [], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 30], // need 500
        ]);

        $this->assertTrue($result['feasible']);
        $this->assertEquals(ORDER_FEASIBILITY_REQUIRE_OPEN, $result['mode']);
    }

    public function test_order_is_impossible_even_with_all_packages()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $this->createOpenBatch($coffeeIngredient, [], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 50], // need 2500
        ]);

        $this->assertFalse($result['feasible']);
        $this->assertEquals(ORDER_FEASIBILITY_IMPOSSIBLE, $result['mode']);
    }

    public function test_order_is_risky_even_with_all_packages()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $user = $this->createUser();

        $coffeeLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $this->createOpenBatch($coffeeIngredient, [], $coffeeLot, $user);

        $product = $this->createProduct([
            'name' => 'Black coffee',
        ], [
            $coffeeIngredient->id => 50,
        ], $coffeeCategory);

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 39], // need 1950
        ]);

        $this->assertTrue($result['feasible']);
        $this->assertEquals(ORDER_FEASIBILITY_RISKY, $result['mode']);
    }

    public function test_low_servings_threshold_triggers_soft_warning()
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

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 8], // need 300
        ]);

        Log::info('Feasibility result: ' . json_encode($result));

        $this->assertTrue($result['feasible']);
        $this->assertEquals(ORDER_FEASIBILITY_RISKY, $result['mode']);
        $this->assertCount(1, $result['ingredient_issues']);
        $this->assertTrue($result['ingredient_issues'][0]['operational_low']);
        $this->assertCount(1, $result['product_caps']);
    }
}
