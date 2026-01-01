<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            IngredientsSeed::class,
            CategoriesSeed::class,
            ProductsSeed::class,
            ProductIngredientsSeed::class,
            UserSeed::class,
        ]);
    }
}
