<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Cà phê đen', 'category_id' => 1, 'selling_price' => 19000],
            ['name' => 'Cà phê sữa', 'category_id' => 1, 'selling_price' => 25000],
            ['name' => 'Bạc xỉu', 'category_id' => 1, 'selling_price' => 29000],
            ['name' => 'Latte', 'category_id' => 1, 'selling_price' => 35000],
            ['name' => 'Cappuccino', 'category_id' => 1, 'selling_price' => 35000],
            ['name' => 'Cold brew', 'category_id' => 1, 'selling_price' => 35000],

            ['name' => 'Trà đào cam sả', 'category_id' => 2, 'selling_price' => 32000],
            ['name' => 'Trà chanh sả', 'category_id' => 2, 'selling_price' => 25000],
            ['name' => 'Trà tắc mật ong', 'category_id' => 2, 'selling_price' => 25000],

            ['name' => 'Matcha latte', 'category_id' => 3, 'selling_price' => 35000],
            ['name' => 'Chocolate sữa', 'category_id' => 3, 'selling_price' => 32000],
            ['name' => 'Sữa tươi trân châu đường đen', 'category_id' => 3, 'selling_price' => 35000],

            ['name' => 'Matcha đá xay', 'category_id' => 4, 'selling_price' => 42000],
            ['name' => 'Chocolate đá xay', 'category_id' => 4, 'selling_price' => 42000],

            ['name' => 'Nước suối', 'category_id' => 5, 'selling_price' => 15000],
            ['name' => 'Cookie', 'category_id' => 6, 'selling_price' => 15000],
            ['name' => 'Bánh chuối', 'category_id' => 6, 'selling_price' => 25000],
            ['name' => 'Bánh mì bơ tỏi', 'category_id' => 6, 'selling_price' => 22000],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
