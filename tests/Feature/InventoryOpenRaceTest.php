<?php

namespace Tests\Feature;

use App\Services\InventoryOpenService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\InventoryTestHelper;
use Tests\TestCase;

class InventoryOpenRaceTest extends TestCase
{
    use RefreshDatabase, InventoryTestHelper;

    public function test_only_one_open_batch_succeeds()
    {
        $ingredient = $this->createIngredient(['name' => 'Milk', 'package_size' => 1000]);
        $this->createUnopenedLot($ingredient, 1);

        $service = app(InventoryOpenService::class);

        $batch1 = $service->open($ingredient->id, 1);

        $this->expectException(DomainException::class);

        $service->open($ingredient->id, 2);

        $this->assertDatabaseHas('inventory_batches', [
            'ingredient_id' => $ingredient->id,
        ]);
    }
}
