<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'active',
        'stock',
        'unlimited_stock',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'unlimited_stock' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function beverageStockEntries()
    {
        return $this->hasMany(BeverageStockEntry::class);
    }

    public function latestBeverageEntry()
    {
        return $this->hasOne(BeverageStockEntry::class)
            ->where('movement_type', 'entry')
            ->latestOfMany();
    }

    public function isBeverage(): bool
    {
        return $this->category?->isBeverages() ?? false;
    }

    public function supportsInfiniteStock(): bool
    {
        return $this->category?->supportsInfiniteStock() ?? false;
    }

    public function hasInfiniteStock(): bool
    {
        return $this->supportsInfiniteStock() && $this->unlimited_stock;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'uploads/')) {
                return asset($this->image);
            }
            return asset('storage/' . $this->image);
        }
        return asset('images/no-image.png');
    }
}
