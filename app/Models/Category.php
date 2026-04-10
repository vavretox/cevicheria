<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public const CODE_BEVERAGES = 'bebidas';
    public const CODE_CEVICHES = 'ceviches';
    public const CODE_ENTRADAS = 'entradas';
    public const CODE_MAIN_DISHES = 'platos_de_fondo';

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('active', true);
    }

    public function isBeverages(): bool
    {
        return $this->code === self::CODE_BEVERAGES;
    }

    public function supportsInfiniteStock(): bool
    {
        return in_array($this->code, [
            self::CODE_CEVICHES,
            self::CODE_ENTRADAS,
            self::CODE_MAIN_DISHES,
        ], true);
    }
}
