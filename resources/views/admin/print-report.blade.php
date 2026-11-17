<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Sembako - {{ $currentMonthName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 p-8 text-xs font-sans">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow border border-gray-200">
        
        <!-- Controls -->
        <div class="no-print flex justify-between items-center pb-6 border-b mb-6">
            <span class="text-xs text-gray-500 font-semibold">Pratinjau Dokumen Cetak / Simpan PDF</span>
            <div class="flex gap-2">
                <button onclick="window.close()" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-lg text-xs font-bold">Tutup</button>
                <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Cetak / Simpan PDF</button>
            </div>
        </div>

        <!-- Document Header -->
        <div class="text-center pb-6 border-b">
            <h1 class="text-xl font-black uppercase tracking-wider text-emerald-800">Toko Sembako Modern</h1>
            <p class="text-xs text-gray-500">Jl. Tebet Raya No. 10, Jakarta Selatan • Telp: 0812-3456-7890</p>
            <h2 class="text-base font-bold text-gray-800 mt-2">LAPORAN REKAPITULASI PENJUALAN BULANAN</h2>
            <p class="text-xs text-gray-600 font-semibold">Periode: {{ $currentMonthName }}</p>
        </div>

        <!-- KPI Summary Boxes -->
        <div class="grid grid-cols-2 gap-4 my-6">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <div class="text-[11px] text-emerald-800 font-semibold uppercase">Total Akumulasi Omzet</div>
                <div class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                <div class="text-[11px] text-gray-600 font-semibold uppercase">Total Transaksi Selesai</div>
                <div class="text-xl font-black text-gray-800 mt-1">{{ $totalOrders }} Pesanan</div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="mb-6">
            <h3 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">5 Produk Terlaris Bulan Ini:</h3>
            <div class="grid grid-cols-5 gap-2">
                @foreach($topProducts as $tp)
                    <div class="p-2 border rounded-lg text-center bg-gray-50">
                        <div class="font-bold truncate text-[11px]">{{ $tp->title }}</div>
                        <div class="text-[10px] text-amber-700 font-bold mt-1">Terjual: {{ $tp->formatted_sold }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Transaction Details Table -->
        <div>
            <h3 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Daftar Transaksi:</h3>
            <table class="w-full text-left text-[11px] border">
                <thead class="bg-gray-100 font-bold border-b">
                    <tr>
                        <th class="p-2">No. Invoice</th>
                        <th class="p-2">Tanggal</th>
                        <th class="p-2">Pelanggan</th>
                        <th class="p-2">Metode</th>
                        <th class="p-2">Status</th>
                        <th class="p-2 text-right">Total Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($orders as $ord)
                        <tr>
                            <td class="p-2 font-mono font-bold">{{ $ord->invoice_no }}</td>
                            <td class="p-2">{{ $ord->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-2">{{ $ord->customer?->name ?? '-' }}</td>
                            <td class="p-2">{{ $ord->payment_method }}</td>
                            <td class="p-2">{{ $ord->status_label }}</td>
                            <td class="p-2 text-right font-bold">Rp {{ number_format($ord->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signature Block -->
        <div class="mt-12 flex justify-between text-xs pt-8 border-t">
            <div>
                <p>Dicetak otomatis oleh Sistem Kasir</p>
                <p class="text-gray-400 text-[10px] mt-1">{{ now()->format('d F Y, H:i:s') }} WIB</p>
            </div>
            <div class="text-center">
                <p>Jakarta, {{ now()->format('d F Y') }}</p>
                <p class="font-bold mt-12 underline">Pemilik Toko Sembako Modern</p>
            </div>
        </div>

    </div>

</body>
</html>

