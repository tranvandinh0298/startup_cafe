<?php

namespace Tests\Helpers;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryLot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductIngredient;

trait InventoryTestHelper
{
    protected function createIngredient(array $overrides = []): Ingredient
    {
        return Ingredient::create(array_merge([
            'name' => 'Ingredient ' . uniqid(),
            'base_unit' => 'g',
            'package_size' => 1000,
            'shelf_life_opened_hours' => 8,
            'shelf_life_closed_days' => 30,
        ], $overrides));
    }

    protected function createUnopenedLot(
        Ingredient $ingredient,
        int $packages
    ): InventoryLot {
        return InventoryLot::create([
            'ingredient_id' => $ingredient->id,
            'quantity_packages' => $packages,
            'received_at' => now(),
            'expired_at' => now()->addDays(10),
        ]);
    }

    protected function createOpenBatch(
        Ingredient $ingredient,
        int $remaining
    ): InventoryBatch {
        return InventoryBatch::create([
            'ingredient_id' => $ingredient->id,
            'inventory_lot_id' => null,
            'opened_at' => now(),
            'expired_at' => now()->addHours(8),
            'status' => 'open',
            'initial_quantity_base' => $remaining,
            'remaining_quantity_base' => $remaining,
            'opened_by' => 1,
        ]);
    }

    protected function createProduct(
        string $name,
        array $recipe // ingredient_id => qty
    ): Product {
        $product = Product::create(['name' => $name]);

        foreach ($recipe as $ingredientId => $qty) {
            ProductIngredient::create([
                'product_id' => $product->id,
                'ingredient_id' => $ingredientId,
                'quantity_per_unit' => $qty,
            ]);
        }

        return $product;
    }

    protected function createOrder(array $items): Order
    {
        $order = Order::create([
            'status' => 'draft',
            'created_at' => now(),
        ]);

        foreach ($items as $productId => $qty) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $qty,
            ]);
        }

        return $order;
    }
}
