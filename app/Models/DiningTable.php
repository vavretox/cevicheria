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
        'merged_into_table_id',
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

    public function mergedInto()
    {
        return $this->belongsTo(self::class, 'merged_into_table_id');
    }

    public function mergedChildren()
    {
        return $this->hasMany(self::class, 'merged_into_table_id');
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

    public function isMergedChild(): bool
    {
        return $this->merged_into_table_id !== null;
    }

    public function hasMergedChildren(): bool
    {
        if ($this->relationLoaded('mergedChildren')) {
            return $this->mergedChildren->isNotEmpty();
        }

        return $this->mergedChildren()->exists();
    }

    public function getMergedMembersAttribute()
    {
        $children = $this->relationLoaded('mergedChildren')
            ? $this->mergedChildren
            : $this->mergedChildren()->get();

        return collect([$this])
            ->merge($children)
            ->sortBy('name')
            ->values();
    }

    public function getMergedDisplayNameAttribute(): string
    {
        return $this->merged_members
            ->pluck('name')
            ->join(' + ');
    }

    public function getCombinedCapacityAttribute(): ?int
    {
        $capacities = $this->merged_members
            ->pluck('capacity')
            ->filter(fn ($capacity) => $capacity !== null);

        if ($capacities->isEmpty()) {
            return null;
        }

        return (int) $capacities->sum();
    }

    public function getGroupReservationSummaryAttribute(): ?string
    {
        $reservedMembers = $this->merged_members
            ->filter(fn (self $table) => $table->isReserved());

        if ($reservedMembers->isEmpty()) {
            return null;
        }

        return $reservedMembers
            ->map(fn (self $table) => $table->name . ': ' . $table->reservation_name)
            ->join(' | ');
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

    public function scopeRoots($query)
    {
        return $query->whereNull('merged_into_table_id');
    }
}
