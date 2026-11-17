<?php

namespace App\Services;

use App\Models\StoreSetting;
use Carbon\Carbon;

class LogisticService
{
    /**
     * Get store settings from database.
     */
    public static function getSettings(): StoreSetting
    {
        return StoreSetting::getSettings();
    }

    /**
     * Check whether the store is currently open based on manual toggle and operating hours.
     */
    public static function isStoreOpen(): bool
    {
        $settings = self::getSettings();

        // If admin manually closed the store
        if (!$settings->is_open) {
            return false;
        }

        $now = now()->timezone('Asia/Jakarta');
        $currentTime = $now->format('H:i:s');

        $openTime = Carbon::parse($settings->open_time)->format('H:i:s');
        $closeTime = Carbon::parse($settings->close_time)->format('H:i:s');

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    /**
     * Get store operating status info.
     *
     * @return array{is_open: bool, label: string, schedule: string, store_name: string, store_address: string}
     */
    public static function getStoreStatus(): array
    {
        $settings = self::getSettings();
        $isOpen = self::isStoreOpen();

        $openFormatted = Carbon::parse($settings->open_time)->format('H:i');
        $closeFormatted = Carbon::parse($settings->close_time)->format('H:i');

        return [
            'is_open' => $isOpen,
            'label' => $isOpen ? 'Toko Buka' : 'Toko Tutup',
            'schedule' => sprintf('Buka setiap hari %s - %s WIB', $openFormatted, $closeFormatted),
            'store_name' => $settings->store_name,
            'store_address' => $settings->store_address,
        ];
    }

    /**
     * Calculate distance between two coordinates in kilometers (Haversine formula).
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Calculate delivery shipping fee based on distance from the dynamic store location in database.
     *
     * @return array{distance_km: float, fee: float, service_fee: float, store_lat: float, store_lng: float}
     */
    public static function calculateFee(float $destLat, float $destLng): array
    {
        $settings = self::getSettings();
        $storeLat = (float) $settings->store_latitude;
        $storeLng = (float) $settings->store_longitude;
        $ratePerKm = (int) ($settings->shipping_rate_per_km ?: 2500);

        $distance = self::calculateDistance($storeLat, $storeLng, $destLat, $destLng);

        $baseFee = 5000; // Tarif dasar untuk km pertama
        $serviceFee = 2000; // Biaya layanan aplikasi

        if ($distance <= 1.0) {
            $shippingFee = $baseFee;
        } else {
            $extraKm = ceil($distance - 1.0);
            $shippingFee = $baseFee + ($extraKm * $ratePerKm);
        }

        return [
            'distance_km' => $distance,
            'fee' => (float) $shippingFee,
            'service_fee' => (float) $serviceFee,
            'store_lat' => $storeLat,
            'store_lng' => $storeLng,
        ];
    }
}
