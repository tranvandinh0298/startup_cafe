<?php

namespace Tests\Unit;

use App\Services\OrderFeasibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class OrderFeasibilityTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_order_is_impossible_even_with_all_packages_opened()
    {
        $coffee = $this->createIngredient(['name' => 'Coffee', 'package_size' => 1000]);

        $this->createOpenBatch($coffee, 100);
        $this->createUnopenedLot($coffee, 1); // +1000

        $product = $this->createProduct('Black coffee', [
            $coffee => 50,
        ]);

        $service = app(OrderFeasibilityService::class);

        $result = $service->analyzeItems([
            ['product_id' => $product->id, 'quantity' => 30], // need 1500
        ]);

        $this->assertFalse($result['feasible']);
        $this->assertEquals('IMPOSSIBLE', $result['mode']);
    }
}
