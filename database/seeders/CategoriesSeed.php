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
            ['name' => 'Cà phê'],
            ['name' => 'Trà'],
            ['name' => 'Sữa'],
            ['name' => 'Đá xay'],
            ['name' => 'Đóng gói'],
            ['name' => 'Bánh ngọt'],
        ];
        foreach ($categories as $data) {
            Category::create($data);
        }
    }
}
