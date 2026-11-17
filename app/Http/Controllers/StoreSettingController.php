<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    /**
     * Show store settings management page.
     */
    public function edit(): View
    {
        $settings = StoreSetting::getSettings();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update store settings and location coordinates.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_address' => ['required', 'string', 'max:1000'],
            'store_latitude' => ['required', 'numeric', 'between:-90,90'],
            'store_longitude' => ['required', 'numeric', 'between:-180,180'],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i'],
            'is_open' => ['nullable', 'boolean'],
            'shipping_rate_per_km' => ['required', 'integer', 'min:500', 'max:50000'],
            'qris_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_address.required' => 'Alamat lengkap toko wajib diisi.',
            'store_latitude.required' => 'Titik koordinat latitude wajib dipilih pada peta.',
            'store_longitude.required' => 'Titik koordinat longitude wajib dipilih pada peta.',
            'open_time.required' => 'Jam buka toko wajib ditentukan.',
            'close_time.required' => 'Jam tutup toko wajib ditentukan.',
            'shipping_rate_per_km.required' => 'Tarif ongkir per km wajib diisi.',
            'shipping_rate_per_km.min' => 'Tarif ongkir minimal Rp 500 per km.',
        ]);

        $settings = StoreSetting::getSettings();
        $settings->update([
            'store_name' => $validated['store_name'],
            'store_address' => $validated['store_address'],
            'store_latitude' => (float) $validated['store_latitude'],
            'store_longitude' => (float) $validated['store_longitude'],
            'open_time' => $validated['open_time'] . ':00',
            'close_time' => $validated['close_time'] . ':00',
            'is_open' => $request->boolean('is_open'),
            'shipping_rate_per_km' => (int) $validated['shipping_rate_per_km'],
        ]);

        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $file->move(public_path('images'), 'qris-barcode.jpg');
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Pengaturan toko, jam operasional, dan titik koordinat GPS berhasil diperbarui.');
    }
}
