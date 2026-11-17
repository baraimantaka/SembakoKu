<?php

namespace Database\Seeders;

use App\Models\CourierLocationLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Default Store Settings (Area Semarang Selatan)
        StoreSetting::updateOrCreate(
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

        // 2. Default Multi-Role Accounts
        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@sembako.test'],
            [
                'name' => 'Pak Joko (Owner Toko)',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'address' => 'Toko Sembako Modern, Jl. Sriwijaya No. 29, Semarang Selatan',
                'latitude' => -7.0051453,
                'longitude' => 110.4381254,
                'is_online' => true,
            ]
        );

        // Courier Driver
        $driver = User::updateOrCreate(
            ['email' => 'driver@sembako.test'],
            [
                'name' => 'Budi Santoso (Kurir)',
                'phone' => '081234567891',
                'password' => Hash::make('password'),
                'role' => 'driver',
                'address' => 'Basecamp Kurir Sembako, Jl. Singosari Raya, Semarang Selatan',
                'latitude' => -7.0070000,
                'longitude' => 110.4365000,
                'is_online' => true,
            ]
        );

        // Customer Pelanggan
        $customer = User::updateOrCreate(
            ['email' => 'pelanggan@sembako.test'],
            [
                'name' => 'Siti Nurhaliza',
                'phone' => '081234567892',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'address' => 'Jl. Erlangga Barat VII No. 12, Pleburan, Kec. Semarang Selatan, Kota Semarang',
                'latitude' => -7.0125000,
                'longitude' => 110.4320000,
                'is_online' => false,
            ]
        );

        // 3. Products Catalog (6 Categories: Beras, Minyak & Bumbu, Telur & Susu, Gas & Galon, Makanan Ringan, Kebersihan)
        $productsData = [
            // Beras
            [
                'title' => 'Beras Pandan Wangi Premium 5kg',
                'category' => 'Beras',
                'unit' => 'kg',
                'price' => 78500,
                'stock' => 45,
                'total_sold' => 482,
                'image_url' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Beras Rojo Lele Super Pulen 5kg',
                'category' => 'Beras',
                'unit' => 'kg',
                'price' => 69000,
                'stock' => 30,
                'total_sold' => 315,
                'image_url' => 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Beras Merah Organik Fresh 2kg',
                'category' => 'Beras',
                'unit' => 'kg',
                'price' => 42000,
                'stock' => 18,
                'total_sold' => 140,
                'image_url' => 'https://images.unsplash.com/photo-1598965675045-45c5e72c7d05?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],

            // Minyak & Bumbu
            [
                'title' => 'Minyak Goreng Bimoli Pouch 2 Liter',
                'category' => 'Minyak & Bumbu',
                'unit' => 'liter',
                'price' => 37500,
                'stock' => 65,
                'total_sold' => 1240,
                'image_url' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Minyak Goreng Sania Royale 2 Liter',
                'category' => 'Minyak & Bumbu',
                'unit' => 'liter',
                'price' => 36000,
                'stock' => 50,
                'total_sold' => 890,
                'image_url' => 'https://images.unsplash.com/photo-1620706857370-e1b9770e8bb1?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Gula Pasir Gulaku Tebu Alami 1kg',
                'category' => 'Minyak & Bumbu',
                'unit' => 'kg',
                'price' => 18500,
                'stock' => 80,
                'total_sold' => 970,
                'image_url' => 'https://images.unsplash.com/photo-1622484212850-eb596d769edc?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Garam Dapur Cap Kapal Halus 250gr',
                'category' => 'Minyak & Bumbu',
                'unit' => 'pcs',
                'price' => 4500,
                'stock' => 120,
                'total_sold' => 450,
                'image_url' => 'https://images.unsplash.com/photo-1518110925495-5fe2fda0442c?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Kecap Manis Bango Botol 520ml',
                'category' => 'Minyak & Bumbu',
                'unit' => 'pcs',
                'price' => 24500,
                'stock' => 40,
                'total_sold' => 610,
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],

            // Telur & Susu
            [
                'title' => 'Telur Ayam Negeri Fresh Pilihan 1kg',
                'category' => 'Telur & Susu',
                'unit' => 'kg',
                'price' => 29500,
                'stock' => 38,
                'total_sold' => 1430,
                'image_url' => 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Susu Ultra Milk UHT Full Cream 1000ml',
                'category' => 'Telur & Susu',
                'unit' => 'pcs',
                'price' => 20500,
                'stock' => 42,
                'total_sold' => 780,
                'image_url' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Susu Kental Manis Frisian Flag Gold 370g',
                'category' => 'Telur & Susu',
                'unit' => 'pcs',
                'price' => 13500,
                'stock' => 3, // Low stock demo (<5)
                'total_sold' => 520,
                'image_url' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],

            // Gas & Galon
            [
                'title' => 'Gas Elpiji 3kg (Melon) Refill Resmi Pertamina',
                'category' => 'Gas & Galon',
                'unit' => 'pcs',
                'price' => 22000,
                'stock' => 20,
                'total_sold' => 960,
                'image_url' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Aqua Air Mineral Galon 19 Liter (Refill)',
                'category' => 'Gas & Galon',
                'unit' => 'galon',
                'price' => 20000,
                'stock' => 25,
                'total_sold' => 1850,
                'image_url' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Le Minerale Galon 15L Sekali Pakai',
                'category' => 'Gas & Galon',
                'unit' => 'galon',
                'price' => 21000,
                'stock' => 15,
                'total_sold' => 630,
                'image_url' => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],

            // Makanan Ringan
            [
                'title' => 'Indomie Mi Instan Goreng Spesial (Dus isi 40)',
                'category' => 'Makanan Ringan',
                'unit' => 'pcs',
                'price' => 118000,
                'stock' => 14,
                'total_sold' => 670,
                'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Biskuit Roma Kelapa Gurih 300g',
                'category' => 'Makanan Ringan',
                'unit' => 'pcs',
                'price' => 11000,
                'stock' => 50,
                'total_sold' => 380,
                'image_url' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],

            // Kebersihan
            [
                'title' => 'Sunlight Pencuci Piring Jeruk Nipis 750ml',
                'category' => 'Kebersihan',
                'unit' => 'pcs',
                'price' => 15500,
                'stock' => 45,
                'total_sold' => 890,
                'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Rinso Molto Deterjen Bubuk Anti Noda 770g',
                'category' => 'Kebersihan',
                'unit' => 'pcs',
                'price' => 23500,
                'stock' => 0, // Out of stock demo
                'total_sold' => 420,
                'image_url' => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=600&q=80',
                'is_active' => true,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $prod) {
            $createdProducts[] = Product::create($prod);
        }

        // 4. Demo Live Order (In Semarang area)
        $order1 = Order::create([
            'invoice_no' => 'INV/' . date('Ymd') . '/SMB/SMG89',
            'customer_id' => $customer->id,
            'courier_id' => $driver->id,
            'subtotal' => 116000,
            'shipping_fee' => 7500,
            'total_price' => 123500,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'order_status' => 'SHIPPED',
            'min_order_satisfied' => true,
            'delivery_address' => $customer->address,
            'delivery_lat' => $customer->latitude,
            'delivery_lng' => $customer->longitude,
            'notes' => 'Beras diikat kencang & tolong jangan dibanting ya mas.',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $createdProducts[0]->id,
            'quantity' => 1,
            'price' => 78500,
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $createdProducts[3]->id,
            'quantity' => 1,
            'price' => 37500,
        ]);

        // Initial courier coordinates log in Semarang
        CourierLocationLog::create([
            'order_id' => $order1->id,
            'latitude' => -7.0065000,
            'longitude' => 110.4370000,
            'created_at' => now()->subMinutes(3),
        ]);
        CourierLocationLog::create([
            'order_id' => $order1->id,
            'latitude' => -7.0090000,
            'longitude' => 110.4350000,
            'created_at' => now(),
        ]);
    }
}
