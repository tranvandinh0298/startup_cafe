<?php

namespace Tests\Unit;

use App\Services\InventoryAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryAvailabilityTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_order_fits_open_inventory()
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

        $order = $this->createOrder([
            $coffeeProduct->id => 2
        ]);

        $inventoryAvailabilityService = app(InventoryAvailabilityService::class);
        $result = $inventoryAvailabilityService->check($order->id);

        $this->assertEquals(true, $result['ok']);
    }

    public function test_order_exceeds_open_inventory()
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
        $this->createOpenBatch($coffeeIngredient, ['remaining_quantity_base' => 50], $inventoryLot, $user);

        $order = $this->createOrder([
            $coffeeProduct->id => 4
        ]);

        $inventoryAvailabilityService = app(InventoryAvailabilityService::class);
        $result = $inventoryAvailabilityService->check($order->id);

        $this->assertEquals(false, $result['ok']);
        $this->assertEquals(
            [
                [
                    'ingredient_id' => $coffeeIngredient->id,
                    'required_base_units' => 18 * 4,
                    'available_base_units' => 50,
                    'missing_base_units' => 18 * 4 - 50
                ]
            ],
            $result['shortages']
        );
    }
}
