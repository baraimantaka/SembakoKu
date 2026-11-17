<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
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
        'title',
        'category',
        'unit',
        'price',
        'stock',
        'total_sold',
        'image_url',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock' => 'integer',
            'total_sold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, ?string $category = null): Builder
    {
        if ($category && $category !== 'Semua') {
            return $query->where('category', $category);
        }

        return $query;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * @return array{text: string, class: string, status: string}
     */
    public function getStockBadgeAttribute(): array
    {
        if ($this->stock <= 0) {
            return [
                'text' => 'Habis',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'status' => 'out_of_stock',
            ];
        }

        if ($this->stock < 5) {
            return [
                'text' => "Stok Menipis ({$this->stock})",
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'status' => 'low_stock',
            ];
        }

        return [
            'text' => 'Tersedia',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'status' => 'available',
        ];
    }

    public function getFormattedSoldAttribute(): string
    {
        $sold = $this->total_sold;
        $unit = $this->unit ?? 'pcs';

        if ($sold >= 1000) {
            $formatted = rtrim(rtrim(number_format($sold / 1000, 1, '.', ''), '0'), '.');
            return "Terjual {$formatted}rb {$unit}";
        }

        return "Terjual {$sold}+ {$unit}";
    }
}

