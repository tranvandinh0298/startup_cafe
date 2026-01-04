<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_code',
        'status',
        'total_amount',
        'inventory_consumed_at'
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (! $order->order_code) {
                $order->order_code = self::generateOrderCode();
            }
        });
    }

    protected static function generateOrderCode(): string
    {
        // Ví dụ: ORD-20260104-0001
        $date = now()->format('Ymd');

        $last = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->value('id');

        $sequence = str_pad(($last ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "ORD-{$date}-{$sequence}";
    }
}
