<?php

namespace Tests\Unit;

use App\Services\ProductAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_product_availability_is_min_of_ingredients()
    {
        $coffee = $this->createIngredient( ['name'=> 'Coffee', 'package_size' => 1000]);
        $milk = $this->createIngredient(['name' => 'Milk', 'package_size' => 1000]);

        $this->createOpenBatch($coffee, 180); // 10 cups
        $this->createOpenBatch($milk, 60); // 2 cups

        $product = $this->createProduct('Cafe sua', [
            $coffee => 18,
            $milk => 30,
        ]);

        $service = app(ProductAvailabilityService::class);
        $result = $service->getAvailability();

        $this->assertEquals(2, $result[$product->id]);
    }
}
