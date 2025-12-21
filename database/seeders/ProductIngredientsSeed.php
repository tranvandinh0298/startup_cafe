<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductIngredientsSeed extends Seeder
{
    public function run(): void
    {
        $map = [

            /* ======================
             | COFFEE
             ====================== */

            'Cà phê đen' => [
                'Cà phê bột' => 18,
                'Đường'     => 15,
            ],

            'Cà phê sữa' => [
                'Cà phê bột' => 18,
                'Sữa đặc'    => 25,
            ],

            'Bạc xỉu' => [
                'Cà phê bột' => 12,
                'Sữa đặc'    => 30,
                'Sữa tươi'   => 120,
            ],

            'Latte' => [
                'Espresso' => 18,
                'Sữa tươi' => 150,
            ],

            'Cappuccino' => [
                'Espresso' => 18,
                'Sữa tươi' => 120,
            ],

            'Cold brew' => [
                'Cold brew concentrate' => 120,
            ],

            /* ======================
             | TEA
             ====================== */

            'Trà đào cam sả' => [
                'Trà ủ'      => 80,
                'Syrup đào'  => 20,
                'Cam'        => 1,
                'Sả'         => 1,
            ],

            'Trà chanh sả' => [
                'Trà ủ' => 80,
                'Chanh' => 0.5,
                'Sả'    => 1,
            ],

            'Trà tắc mật ong' => [
                'Trà ủ'    => 50,
                'Tắc'      => 3,
                'Mật ong'  => 15,
            ],

            /* ======================
             | MILK / MATCHA / CACAO
             ====================== */

            'Matcha latte' => [
                'Matcha'   => 2,
                'Sữa tươi' => 180,
            ],

            'Chocolate sữa' => [
                'Cacao'    => 8,
                'Sữa tươi' => 180,
            ],

            'Sữa tươi trân châu đường đen' => [
                'Sữa tươi'        => 150,
                'Trân châu'       => 60,
                'Syrup đường đen' => 20,
            ],

            /* ======================
             | ICE BLENDED
             ====================== */

            'Matcha đá xay' => [
                'Matcha'     => 2,
                'Sữa tươi'   => 150,
                'Bột frappe' => 15,
            ],

            'Chocolate đá xay' => [
                'Cacao'      => 8,
                'Sữa tươi'   => 150,
                'Bột frappe' => 15,
            ],

            /* ======================
             | PACKAGED / BAKERY
             ====================== */

            // Đồ đóng gói: 1 sản phẩm = 1 đơn vị
            'Nước suối' => [
                'Nước suối' => 1,
            ],

            'Cookie' => [
                'Cookie' => 1,
            ],

            'Bánh chuối' => [
                'Bánh chuối' => 1,
            ],

            'Bánh mì bơ tỏi' => [
                'Bánh mì bơ tỏi' => 1,
            ],
        ];

        foreach ($map as $productName => $ingredients) {
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                throw new \Exception("Product not found: {$productName}");
            }

            foreach ($ingredients as $ingredientName => $quantity) {
                $ingredient = Ingredient::where('name', $ingredientName)->first();

                if (!$ingredient) {
                    throw new \Exception("Ingredient not found: {$ingredientName}");
                }

                $product->productIngredients()->create([
                    'ingredient_id'    => $ingredient->id,
                    'quantity_per_unit' => $quantity,
                ]);
            }
        }
    }
}
