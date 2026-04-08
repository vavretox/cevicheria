<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ORDER_CHANNEL_TABLE = 'table';
    public const ORDER_CHANNEL_DELIVERY = 'delivery';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'order_channel',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCajero()
    {
        return $this->role === 'cajero';
    }

    public function isMesero()
    {
        return $this->role === 'mesero';
    }

    public function isDeliveryWaiter(): bool
    {
        return $this->isMesero() && $this->order_channel === self::ORDER_CHANNEL_DELIVERY;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function processedOrders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function cashSessions()
    {
        return $this->hasMany(CashSession::class, 'user_id');
    }
}
