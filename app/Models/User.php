<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'address',
        'latitude',
        'longitude',
        'is_online',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_online' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function courierOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin';
    }

    public function isCourier(): bool
    {
        return in_array(strtolower($this->role ?? ''), ['driver', 'courier']);
    }

    public function isDriver(): bool
    {
        return $this->isCourier();
    }

    public function isCustomer(): bool
    {
        return strtolower($this->role ?? 'customer') === 'customer';
    }

    public function getRoleLabelAttribute(): string
    {
        return match (strtolower($this->role ?? '')) {
            'admin' => 'Admin Kasir',
            'driver', 'courier' => 'Kurir Driver',
            default => 'Pelanggan',
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match (strtolower($this->role ?? '')) {
            'admin' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
            'driver', 'courier' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            default => 'bg-brand-500/20 text-brand-300 border-brand-500/30',
        };
    }
}
