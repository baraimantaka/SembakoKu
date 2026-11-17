<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'store_address',
        'store_latitude',
        'store_longitude',
        'open_time',
        'close_time',
        'is_open',
        'shipping_rate_per_km',
    ];

    protected function casts(): array
    {
        return [
            'store_latitude' => 'float',
            'store_longitude' => 'float',
            'is_open' => 'boolean',
            'shipping_rate_per_km' => 'integer',
        ];
    }

    /**
     * Get the dynamic public URL of the active QRIS barcode image.
     */
    public function getQrisUrlAttribute(): string
    {
        $path = public_path('images/qris-barcode.jpg');
        $version = file_exists($path) ? filemtime($path) : time();

        return asset('images/qris-barcode.jpg') . '?v=' . $version;
    }

    /**
     * Get or create the singleton store settings record.
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Toko Sembako Modern (Semarang Selatan)',
                'store_address' => 'Jl. Sriwijaya No. 29, Pleburan, Kec. Semarang Selatan, Kota Semarang, Jawa Tengah 50242',
                'store_latitude' => -7.0051453,
                'store_longitude' => 110.4381254,
                'open_time' => '07:00:00',
                'close_time' => '21:00:00',
                'is_open' => true,
                'shipping_rate_per_km' => 2500,
            ]
        );
    }
}
