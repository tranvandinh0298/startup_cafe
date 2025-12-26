<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'base_unit',
        'package_size',
        'shelf_life_closed_days',
        'shelf_life_opened_hours',
        'reorder_threshold'
    ];

    public function inventoryLots(): HasMany
    {
        return $this->hasMany(InventoryLot::class, 'ingredient_id');
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'ingredient_id');
    }

    public function productIngredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class, 'ingredient_id');
    }

    public function inventoryActions(): HasMany
    {
        return $this->hasMany(InventoryAction::class, 'ingredient_id');
    }
}
