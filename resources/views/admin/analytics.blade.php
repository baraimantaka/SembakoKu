@extends('layouts.app')

@section('title', 'Laporan Bulanan & Analisis Penjualan - Toko Sembako Modern')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER & EXPORT ACTIONS -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-lg font-extrabold text-gray-900 tracking-tight">Analisis Penjualan & Laporan Bulanan</h1>
                    <p class="text-xs text-gray-500">Statistik performa omzet, order, produk terlaris, dan export data keuangan</p>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.orders') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                    <span>Pesanan Masuk</span>
                </a>
                <a href="{{ route('admin.products') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Katalog Produk</span>
                </a>
                <a href="{{ route('admin.couriers') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="bike" class="w-4 h-4"></i>
                    <span>Peta Multi-Kurir</span>
                </a>
                <a href="{{ route('admin.analytics') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white shadow-sm flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Laporan & KPI</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Pengaturan Toko</span>
                </a>
                <!-- Export Excel -->
                <a href="{{ route('admin.export-report') }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md flex-shrink-0">
                    <i data-lucide="sheet" class="w-4 h-4"></i>
                    <span>Excel</span>
                </a>
                <!-- Print / PDF Report -->
                <a href="{{ route('admin.print-report') }}" target="_blank" class="px-3 py-2 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md flex-shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>PDF</span>
                </a>
            </div>
        </div>

        <!-- 1. KPI CARDS METRICS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Omzet Bulanan -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Omzet Bulan Ini</span>
                    <div class="text-2xl font-black text-brand-600 mt-1">
                        Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        <span>Pertumbuhan penjualan stabil</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Pesanan Selesai -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pesanan Selesai Dikirim</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $completedOrdersCount }} Pesanan
                    </div>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Tingkat keberhasilan delivery 98.4%
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>

            <!-- Average Order Value (AOV) -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-Rata Nilai Belanja (AOV)</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Per transaksi pelanggan
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
            </div>

        </div>

        <!-- 2. REVENUE CHART (CHART.JS) -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-brand-50 text-brand-600">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-sm">Grafik Trend Pendapatan Harian</h3>
                        <p class="text-xs text-gray-400">Akumulasi transaksi sembako terbayar bulan ini</p>
                    </div>
                </div>
            </div>

            <div class="h-64 sm:h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- 3. TOP SELLING PRODUCTS RANKING TABLE -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-sm">Peringkat Produk Sembako Terlaris (Top Selling)</h3>
                        <p class="text-xs text-gray-400">Diurutkan berdasarkan unit akumulasi penjualan terbanyak</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">Rank</th>
                            <th class="py-3.5 px-4">Nama Produk</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga Satuan</th>
                            <th class="py-3.5 px-4">Sisa Stok</th>
                            <th class="py-3.5 px-4">Total Terjual</th>
                            <th class="py-3.5 px-4 text-right">Est. Omzet Produk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topProducts as $index => $prod)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold text-xs
                                        @if($index === 0) bg-amber-100 text-amber-800 ring-2 ring-amber-300
                                        @elseif($index === 1) bg-gray-200 text-gray-700
                                        @elseif($index === 2) bg-amber-50 text-amber-700
                                        @else bg-gray-100 text-gray-500 @endif">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-gray-900">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $prod->image_url }}" class="w-8 h-8 rounded-lg object-cover border">
                                        <span>{{ $prod->title }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">{{ $prod->category }}</td>
                                <td class="py-3.5 px-4 font-bold text-gray-900">{{ $prod->formatted_price }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold {{ $prod->stock <= 0 ? 'text-rose-600' : ($prod->stock < 5 ? 'text-amber-600' : 'text-gray-900') }}">
                                        {{ $prod->stock }} {{ $prod->unit }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 font-extrabold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                        <i data-lucide="flame" class="w-3.5 h-3.5 text-amber-500"></i>
                                        <span>{{ $prod->formatted_sold }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-brand-600">
                                    Rp {{ number_format($prod->price * $prod->total_sold, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const labels = @json($chartLabels);
    const data = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan Harian (Rp)',
                data: data,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#059669',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return 'Rp ' + Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

