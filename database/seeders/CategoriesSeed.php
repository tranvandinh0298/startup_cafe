<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Coffee'],
            ['name' => 'Tea'],
            ['name' => 'Milk'],
            ['name' => 'IceBlended'],
            ['name' => 'Packaged'],
            ['name' => 'Bakery'],
        ];
        foreach ($categories as $data) {
            Category::create($data);
        }
    }
}
