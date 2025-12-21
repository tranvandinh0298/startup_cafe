<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $ingredients = [
            // Dairy
            [
                'name' => 'Sữa tươi',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 90,
                'shelf_life_opened_hours' => 48,
                'reorder_threshold' => 3,
            ],
            [
                'name' => 'Sữa đặc',
                'base_unit' => 'g',
                'package_size' => 380,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 72,
                'reorder_threshold' => 3,
            ],

            // Coffee & tea
            [
                'name' => 'Cà phê bột',
                'base_unit' => 'g',
                'package_size' => 1000,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 168,
                'reorder_threshold' => 2,
            ],
            [
                'name' => 'Espresso',
                'base_unit' => 'g',
                'package_size' => 250,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 72,
                'reorder_threshold' => 2,
            ],
            [
                'name' => 'Cold brew concentrate',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 30,
                'shelf_life_opened_hours' => 72,
                'reorder_threshold' => 2,
            ],
            [
                'name' => 'Trà ủ',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 7,
                'shelf_life_opened_hours' => 24,
                'reorder_threshold' => 2,
            ],

            // Syrup & topping
            [
                'name' => 'Syrup đào',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 365,
                'shelf_life_opened_hours' => 720,
                'reorder_threshold' => 1,
            ],
            [
                'name' => 'Syrup đường đen',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 365,
                'shelf_life_opened_hours' => 720,
                'reorder_threshold' => 1,
            ],
            [
                'name' => 'Trân châu',
                'base_unit' => 'g',
                'package_size' => 1000,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 6,
                'reorder_threshold' => 2,
            ],
            [
                'name' => 'Mật ong',
                'base_unit' => 'ml',
                'package_size' => 1000,
                'shelf_life_closed_days' => 365,
                'shelf_life_opened_hours' => 8760,
                'reorder_threshold' => 1,
            ],

            // Powder
            [
                'name' => 'Matcha',
                'base_unit' => 'g',
                'package_size' => 100,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 168,
                'reorder_threshold' => 1,
            ],
            [
                'name' => 'Cacao',
                'base_unit' => 'g',
                'package_size' => 500,
                'shelf_life_closed_days' => 180,
                'shelf_life_opened_hours' => 168,
                'reorder_threshold' => 1,
            ],
            [
                'name' => 'Bột frappe',
                'base_unit' => 'g',
                'package_size' => 1000,
                'shelf_life_closed_days' => 365,
                'shelf_life_opened_hours' => 720,
                'reorder_threshold' => 1,
            ],
            [
                'name' => 'Đường',
                'base_unit' => 'g',
                'package_size' => 1000,
                'shelf_life_closed_days' => 365,
                'shelf_life_opened_hours' => 720,
                'reorder_threshold' => 1,
            ],

            // Fruit & others
            ['name' => 'Cam', 'base_unit' => 'piece', 'package_size' => 1, 'shelf_life_closed_days' => 7, 'shelf_life_opened_hours' => 24, 'reorder_threshold' => 10],
            ['name' => 'Chanh', 'base_unit' => 'piece', 'package_size' => 1, 'shelf_life_closed_days' => 7, 'shelf_life_opened_hours' => 24, 'reorder_threshold' => 10],
            ['name' => 'Tắc', 'base_unit' => 'piece', 'package_size' => 1, 'shelf_life_closed_days' => 7, 'shelf_life_opened_hours' => 24, 'reorder_threshold' => 20],
            ['name' => 'Sả', 'base_unit' => 'stalk', 'package_size' => 1, 'shelf_life_closed_days' => 7, 'shelf_life_opened_hours' => 24, 'reorder_threshold' => 10],

            // Packaged
            ['name' => 'Nước suối', 'base_unit' => 'bottle', 'package_size' => 1, 'shelf_life_closed_days' => 365, 'shelf_life_opened_hours' => 0, 'reorder_threshold' => 20],
            ['name' => 'Cookie', 'base_unit' => 'pack', 'package_size' => 1, 'shelf_life_closed_days' => 60, 'shelf_life_opened_hours' => 0, 'reorder_threshold' => 20],
            ['name' => 'Bánh chuối', 'base_unit' => 'pack', 'package_size' => 1, 'shelf_life_closed_days' => 5, 'shelf_life_opened_hours' => 0, 'reorder_threshold' => 10],
            ['name' => 'Bánh mì bơ tỏi', 'base_unit' => 'pack', 'package_size' => 1, 'shelf_life_closed_days' => 3, 'shelf_life_opened_hours' => 0, 'reorder_threshold' => 10],
        ];

        foreach ($ingredients as $data) {
            Ingredient::create($data);
        }
    }
}
