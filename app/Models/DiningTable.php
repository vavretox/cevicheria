<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'name',
        'zone',
        'capacity',
        'active',
        'reservation_name',
        'reservation_at',
        'reservation_notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'capacity' => 'integer',
        'reservation_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrders()
    {
        return $this->hasMany(Order::class, 'table_id')
            ->whereIn('status', ['pending', 'processing']);
    }

    public function isReserved(): bool
    {
        return !empty($this->reservation_name) && $this->reservation_at !== null;
    }

    public function getUiStatusAttribute(): string
    {
        if (!$this->active) {
            return 'closed';
        }

        if (($this->active_orders_count ?? 0) > 0) {
            return 'occupied';
        }

        if ($this->isReserved()) {
            return 'reserved';
        }

        return 'available';
    }
}
