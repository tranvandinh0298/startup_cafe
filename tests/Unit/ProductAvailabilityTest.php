<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\InventoryOpenService;
use App\Services\ProductAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    // public function test_product_availability_is_min_of_ingredients()
    // {
    //     $coffee = $this->createIngredient( ['name'=> 'Coffee', 'package_size' => 1000]);
    //     $milk = $this->createIngredient(['name' => 'Milk', 'package_size' => 1000]);

    //     $this->createOpenBatch($coffee, 180); // 10 cups
    //     $this->createOpenBatch($milk, 60); // 2 cups

    //     $product = $this->createProduct('Cafe sua', [
    //         $coffee => 18,
    //         $milk => 30,
    //     ]);

    //     $service = app(ProductAvailabilityService::class);
    //     $result = $service->getAvailability();

    //     $this->assertEquals(2, $result[$product->id]);
    // }

    public function test_single_product_single_ingredient_fail_for_not_open_any_batch()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient([
            'name' => 'Coffee',
            'package_size' => 1000
        ]);

        $coffeeProduct = $this->createProduct(
            [
                'name' => 'Cà phê đen'
            ],
            [$coffeeIngredient->id => 18],
            $coffeeCategory
        );

        $service = app(ProductAvailabilityService::class);
        $result = $service->getAvailability();

        $this->assertEquals(0, $result[$coffeeProduct->id]);
    }

    public function test_single_product_single_ingredient_pass()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient([
            'name' => 'Coffee',
            'package_size' => 1000
        ]);

        $coffeeProduct = $this->createProduct(
            [
                'name' => 'Cà phê đen'
            ],
            [$coffeeIngredient->id => 18],
            $coffeeCategory
        );

        $user = $this->createUser();

        $inventoryLot = $this->createUnopenedLot($coffeeIngredient, 1); // +1000
        $this->createOpenBatch($coffeeIngredient, [], $inventoryLot, $user);

        $productAvailabilityService = app(ProductAvailabilityService::class);
        $result = $productAvailabilityService->getAvailability();

        $this->assertEquals(55, $result[$coffeeProduct->id]);
    }

    public function test_single_product_multiple_ingredient()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient([
            'name' => 'Coffee',
            'package_size' => 1000
        ]);

        $milkIngredient = $this->createIngredient([
            'name' => 'Milk',
            'base_unit' => 'ml',
            'package_size' => 1000
        ]);

        $milkCoffeProduct = $this->createProduct(
            [
                'name' => 'Cà phê đen'
            ],
            [
                $coffeeIngredient->id => 18,
                $milkIngredient->id => 30
            ],
            $coffeeCategory
        );

        $user = $this->createUser();

        $coffeeInventoryLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $milkInventoryLot = $this->createUnopenedLot($milkIngredient, 1);
        $this->createOpenBatch(
            $coffeeIngredient,
            [
                'remaining_quantity_base' => 180,
            ],
            $coffeeInventoryLot,
            $user
        );
        $this->createOpenBatch(
            $milkIngredient,
            [
                'remaining_quantity_base' => 60,
            ],
            $milkInventoryLot,
            $user
        );

        $productAvailabilityService = app(ProductAvailabilityService::class);
        $result = $productAvailabilityService->getAvailability();

        $this->assertEquals(2, $result[$milkCoffeProduct->id]);
    }

    public function test_single_product_multiple_ingredient_but_one_ingredient_is_expired()
    {
        $coffeeCategory = $this->createCategory([
            'name' => 'Coffee'
        ]);

        $coffeeIngredient = $this->createIngredient([
            'name' => 'Coffee',
            'package_size' => 1000
        ]);

        $milkIngredient = $this->createIngredient([
            'name' => 'Milk',
            'base_unit' => 'ml',
            'package_size' => 1000
        ]);

        $milkCoffeProduct = $this->createProduct(
            [
                'name' => 'Cà phê đen'
            ],
            [
                $coffeeIngredient->id => 18,
                $milkIngredient->id => 30
            ],
            $coffeeCategory
        );

        $user = $this->createUser();

        $coffeeInventoryLot = $this->createUnopenedLot($coffeeIngredient, 1);
        $milkInventoryLot = $this->createUnopenedLot($milkIngredient, 1);
        $this->createOpenBatch(
            $coffeeIngredient,
            [
                'expired_at' => now()
            ],
            $coffeeInventoryLot,
            $user
        );
        $this->createOpenBatch(
            $milkIngredient,
            [
                'remaining_quantity_base' => 60,
            ],
            $milkInventoryLot,
            $user
        );

        $productAvailabilityService = app(ProductAvailabilityService::class);
        $result = $productAvailabilityService->getAvailability();

        $this->assertEquals(0, $result[$milkCoffeProduct->id]);
    }
}
