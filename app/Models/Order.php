<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'courier_id',
        'subtotal',
        'shipping_fee',
        'total_price',
        'payment_method',
        'payment_status',
        'order_status',
        'min_order_satisfied',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'notes',
        'proof_image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'shipping_fee' => 'float',
            'total_price' => 'float',
            'min_order_satisfied' => 'boolean',
            'delivery_lat' => 'float',
            'delivery_lng' => 'float',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(CourierLocationLog::class, 'order_id')->orderBy('created_at', 'desc');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedShippingFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_fee, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            'PENDING' => 'Menunggu Pembayaran',
            'CONFIRMED' => 'Pesanan Dikonfirmasi',
            'PROCESSING' => 'Sedang Dikemas',
            'SHIPPED' => 'Dalam Pengantaran',
            'DELIVERED' => 'Pesanan Selesai',
            'CANCELLED' => 'Pesanan Dibatalkan',
            default => $this->order_status,
        };
    }

    public function getStatusStepIndexAttribute(): int
    {
        return match ($this->order_status) {
            'PENDING' => 1,
            'CONFIRMED' => 2,
            'PROCESSING' => 3,
            'SHIPPED' => 4,
            'DELIVERED' => 5,
            default => 0,
        };
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'INV/' . date('Ymd') . '/SMB/';
        $random = strtoupper(bin2hex(random_bytes(3)));
        return $prefix . $random;
    }
}

