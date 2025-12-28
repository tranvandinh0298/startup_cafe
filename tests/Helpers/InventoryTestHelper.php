<?php

namespace Tests\Helpers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\InventoryLot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\User;
use Illuminate\Support\Str;

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
        array $overrides,
        InventoryLot $inventoryLot,
        $user
    ): InventoryBatch {
        return InventoryBatch::create( array_merge([
            'ingredient_id' => $ingredient->id,
            'inventory_lot_id' => $inventoryLot->id,
            'opened_at' => now(),
            'expired_at' => now()->addHours(8),
            'status' => PACKAGE_STATUS_OPEN,
            'initial_quantity_base' => 1000,
            'remaining_quantity_base' => 1000,
            'opened_by' => $user->id,
        ], $overrides));
    }

    protected function createProduct(
        array $overrides,
        array $recipe, // ingredient_id => qty,
        Category $category
    ): Product {
        $product = Product::create(array_merge([
            'name' => 'Product ' . uniqid(),
            'category_id' => $category->id,
            'selling_price' => 10000
        ], $overrides));

        foreach ($recipe as $ingredientId => $qty) {
            ProductIngredient::create([
                'product_id' => $product->id,
                'ingredient_id' => $ingredientId,
                'quantity_per_unit' => $qty,
            ]);
        }

        return $product;
    }

    protected function createOrder(array $items, $orderStatus = ORDER_STATUS_DEFAULT): Order
    {
        $order = Order::create([
            'order_code' => 'ORDER_' . uniqid(),
            'status' => $orderStatus,
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

    protected function createCategory(array $overrides = []): Category
    {
        $category = Category::create(array_merge([
            'name' => 'Category ' . uniqid(),
            'status' => RECORD_STATUS_DEFAULT
        ], $overrides));

        return $category;
    }

    protected function createUser()
    {
        return User::create([
            'name' => "User " . uniqid(),
            'email' => "user" . uniqid() . "@example.com",
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ]);
    }
}
