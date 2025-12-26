<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBatch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_batches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ingredient_id',
        'inventory_lot_id',
        'initial_quantity_base',
        'remaining_quantity_base',
        'opened_at',
        'expired_at',
        'status',
        'opened_by'
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function inventoryLots(): HasMany
    {
        return $this->hasMany(InventoryLot::class, 'inventory_batch_id');
    }

    public function openBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}
