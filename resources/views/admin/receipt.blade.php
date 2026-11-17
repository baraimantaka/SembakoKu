<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Thermal - {{ $order->invoice_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Thermal 58mm / 80mm Print CSS */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-paper {
                width: 72mm !important; /* Standard 80mm printable width */
                max-width: 72mm !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
        }

        .receipt-paper {
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.25;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 flex flex-col items-center justify-start">

    <!-- ACTION CONTROLS (NO PRINT) -->
    <div class="no-print max-w-md w-full mb-6 bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="font-bold text-gray-900 text-xs">Cetak Struk Kasir Thermal</div>
                <div class="text-[11px] text-gray-500">Format ESC-POS 58mm / 80mm</div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Bluetooth ESC/POS Print -->
            <button onclick="printViaBluetooth()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="bluetooth" class="w-3.5 h-3.5"></i>
                <span>Web Bluetooth</span>
            </button>
            
            <!-- Standard System / Thermal Print -->
            <button onclick="window.print()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- PHYSICAL THERMAL RECEIPT PAPER -->
    <div class="receipt-paper w-[80mm] bg-white p-4 shadow-xl border border-gray-200 text-gray-900 text-xs">
        
        <!-- STORE HEADER -->
        <div class="text-center pb-2">
            <h1 class="font-black text-sm uppercase tracking-wider">{{ $storeName }}</h1>
            <p class="text-[11px] mt-0.5">{{ $storeAddress }}</p>
            <p class="text-[11px]">Telp: {{ $storePhone }}</p>
        </div>

        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- TRANSACTION META -->
        <div class="space-y-0.5 text-[11px]">
            <div class="flex justify-between">
                <span>No. Invoice:</span>
                <span class="font-bold">{{ $order->invoice_no }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir / Toko:</span>
                <span>Admin SembakoKu</span>
            </div>
            <div class="flex justify-between">
                <span>Kurir:</span>
                <span>{{ $order->courier?->name ?? 'Armada Toko' }}</span>
            </div>
            <div class="pt-1">
                <div class="text-gray-600">Pelanggan:</div>
                <div class="font-bold">{{ $order->customer?->name }} ({{ $order->customer?->phone }})</div>
                <div class="text-[10px] text-gray-600 truncate">{{ $order->delivery_address }}</div>
            </div>
        </div>

        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- ITEMS LIST -->
        <div class="space-y-1.5 text-[11px]">
            @foreach($order->items as $item)
                <div>
                    <div class="font-bold truncate">{{ $item->product?->title ?? 'Produk' }}</div>
                    <div class="flex justify-between text-gray-700 pl-2">
                        <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- FINANCIAL SUMMARY -->
        <div class="space-y-0.5 text-[11px]">
            <div class="flex justify-between">
                <span>Subtotal Produk:</span>
                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Ongkos Kirim:</span>
                <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-black text-xs pt-1 border-t border-gray-300">
                <span>TOTAL BAYAR:</span>
                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- PAYMENT METHOD & STATUS -->
        <div class="text-center font-bold text-xs py-1">
            @if($order->payment_method === 'QRIS')
                <div class="uppercase">METODE: QRIS [{{ $order->payment_status === 'PAID' ? 'LUNAS' : 'PENDING' }}]</div>
            @else
                <div class="uppercase text-rose-700">METODE: COD (BAYAR TUNAI)</div>
                <div class="text-[11px] text-gray-800 font-semibold mt-0.5">
                    {{ $order->payment_status === 'PAID' ? '[SUDAH DITERIMA KURIR]' : 'TAGIH KE PELANGGAN: Rp ' . number_format($order->total_price, 0, ',', '.') }}
                </div>
            @endif
        </div>

        <div class="border-t border-dashed border-gray-400 my-2"></div>

        <!-- FOOTER MESSAGE -->
        <div class="text-center text-[10px] text-gray-600 space-y-1 pt-1">
            <p>Terima kasih atas pesanan Anda!</p>
            <p>Barang sembako segar & berkualitas diantar langsung dari Toko Sembako Modern.</p>
            <p class="font-mono text-[9px] text-gray-400 pt-1">*** SIMPAN STRUK INI SEBAGAI BUKTI PEMBELIAN ***</p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        // Web Bluetooth API ESC/POS Printing Integration
        async function printViaBluetooth() {
            if (!navigator.bluetooth) {
                alert('Browser Anda tidak mendukung Web Bluetooth API. Silakan gunakan tombol "Cetak Struk" biasa.');
                return;
            }

            try {
                const device = await navigator.bluetooth.requestDevice({
                    filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });

                const server = await device.gatt.connect();
                alert(`Terhubung ke printer bluetooth: ${device.name}. Mengirim perintah cetak ESC/POS...`);
                // Note: ESC/POS byte sequence generation
            } catch (error) {
                console.warn('Bluetooth connection cancelled or unavailable', error);
                // Fallback to standard print
                window.print();
            }
        }
    </script>
</body>
</html>

