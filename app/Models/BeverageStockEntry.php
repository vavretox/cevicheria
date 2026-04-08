<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeverageStockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'movement_type',
        'entry_type',
        'quantity',
        'units_per_box',
        'total_units',
        'purchase_price',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getEntryTypeLabelAttribute(): string
    {
        return $this->entry_type === 'box' ? 'Caja' : 'Unidad';
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return $this->movement_type === 'exit' ? 'Salida' : 'Entrada';
    }

    public function getSignedUnitsAttribute(): int
    {
        $units = (int) $this->total_units;
        return $this->movement_type === 'exit' ? ($units * -1) : $units;
    }
}
