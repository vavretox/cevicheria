<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'opening_amount',
        'opening_note',
        'opened_at',
        'closed_at',
        'expected_amount',
        'counted_amount',
        'difference_amount',
        'closing_note',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'counted_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'cash_session_id');
    }

    public function getSalesTotalAttribute(): float
    {
        if (array_key_exists('orders_sum_total', $this->attributes)) {
            return (float) $this->attributes['orders_sum_total'];
        }

        return (float) $this->orders()->where('status', 'completed')->sum('total');
    }

    public function getExpectedBalanceAttribute(): float
    {
        if ($this->status === 'closed' && $this->expected_amount !== null) {
            return (float) $this->expected_amount;
        }

        return (float) $this->opening_amount + $this->sales_total;
    }
}
