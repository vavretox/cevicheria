<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'service_type',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return $this->service_type === 'takeaway' ? 'Para llevar' : 'En mesa';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detail) {
            $detail->subtotal = $detail->quantity * $detail->unit_price;
        });

        static::updating(function ($detail) {
            $detail->subtotal = $detail->quantity * $detail->unit_price;
        });
    }
}
