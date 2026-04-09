<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cashier_id',
        'cash_session_id',
        'table_id',
        'table_number',
        'service_mode',
        'order_date',
        'daily_sequence',
        'subtotal',
        'total',
        'status',
        'payment_method',
        'amount_received',
        'cash_paid_amount',
        'qr_paid_amount',
        'change_amount',
        'completed_at',
        'revert_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'cash_paid_amount' => 'decimal:2',
        'qr_paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function audits()
    {
        return $this->hasMany(OrderAudit::class);
    }

    public function latestAudit()
    {
        return $this->hasOne(OrderAudit::class)->latestOfMany('created_at');
    }

    public function calculateTotal()
    {
        $this->subtotal = $this->details->sum('subtotal');
        $this->total = $this->subtotal;
        $this->save();
    }

    public function getTableLabelAttribute(): ?string
    {
        return $this->diningTable?->merged_display_name ?? $this->table_number;
    }

    public function getServiceModeLabelAttribute(): string
    {
        $mode = $this->service_mode;

        if (!$mode && $this->relationLoaded('details')) {
            $mode = $this->inferServiceModeFromItems($this->details);
        }

        return match ($mode) {
            'takeaway' => 'Solo para llevar',
            'mixed' => 'Mesa y para llevar',
            default => 'Solo mesa',
        };
    }

    public function getDisplayNumberAttribute(): string
    {
        $date = $this->order_date ?? $this->created_at;
        $prefix = $date ? $date->format('dmy') : '000000';
        $sequence = str_pad((string) ($this->daily_sequence ?? $this->id ?? 0), 3, '0', STR_PAD_LEFT);

        return $prefix . '-' . $sequence;
    }

    protected static function booted()
    {
        static::creating(function (self $order) {
            if (!$order->order_date) {
                $order->order_date = now()->toDateString();
            }

            if (!$order->daily_sequence) {
                $maxSequence = static::query()
                    ->whereDate('order_date', $order->order_date)
                    ->lockForUpdate()
                    ->max('daily_sequence');

                $order->daily_sequence = ((int) $maxSequence) + 1;
            }
        });
    }

    public function inferServiceModeFromItems($items): string
    {
        $hasDineIn = collect($items)->contains(fn ($item) => ($item->service_type ?? $item['service_type'] ?? 'dine_in') === 'dine_in');
        $hasTakeaway = collect($items)->contains(fn ($item) => ($item->service_type ?? $item['service_type'] ?? 'dine_in') === 'takeaway');

        if ($hasDineIn && $hasTakeaway) {
            return 'mixed';
        }

        if ($hasTakeaway) {
            return 'takeaway';
        }

        return 'dine_in';
    }
}
