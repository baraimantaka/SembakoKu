@extends('layouts.app')

@section('title', 'Admin POS Kasir & Live Orders - Toko Sembako Modern')

@section('content')
<div x-data="adminOrdersApp()" x-init="init()" class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- ADMIN TOP NAVIGATION BAR -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-lg shadow-brand-600/30">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-gray-900 tracking-tight">POS Kasir & Manajemen Pesanan Toko</h1>
                    <p class="text-xs text-gray-500">Live order management, dispatching kurir & kasir fisik</p>
                </div>
            </div>

            <!-- Sub Nav Links -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.orders') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white shadow-sm flex items-center gap-1.5 flex-shrink-0">
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
                <a href="{{ route('admin.analytics') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Laporan & KPI</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Pengaturan Toko</span>
                </a>
            </div>
        </div>

        <!-- SOUND ALERT & REALTIME INDICATOR BANNER -->
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white rounded-3xl p-4 shadow-md flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <div class="text-xs">
                    <span class="font-bold text-emerald-400">POS Audio Sound Alert Aktif</span>
                    <span class="text-gray-400 hidden sm:inline ml-1">• Sistem otomatis membunyikan bel transaksi baru & memantau pesanan masuk secara live.</span>
                </div>
            </div>

            <button 
                @click="playTestSound()" 
                class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
                <i data-lucide="volume-2" class="w-3.5 h-3.5 text-accent-400"></i>
                <span>Test Bel Kasir</span>
            </button>
        </div>

        <!-- STATUS TABS FILTER -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <a href="{{ route('admin.orders', ['status' => 'ALL']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'ALL' ? 'bg-brand-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Semua</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'ALL' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['ALL'] }}</span>
            </a>
            <a href="{{ route('admin.orders', ['status' => 'PENDING']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'PENDING' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Menunggu Konfirmasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'PENDING' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $counts['PENDING'] }}</span>
            </a>
            <a href="{{ route('admin.orders', ['status' => 'CONFIRMED']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'CONFIRMED' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Dikonfirmasi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'CONFIRMED' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">{{ $counts['CONFIRMED'] }}</span>
            </a>
            <a href="{{ route('admin.orders', ['status' => 'PROCESSING']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'PROCESSING' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Sedang Dikemas</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'PROCESSING' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-800' }}">{{ $counts['PROCESSING'] }}</span>
            </a>
            <a href="{{ route('admin.orders', ['status' => 'SHIPPED']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'SHIPPED' ? 'bg-brand-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Dalam Pengantaran</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'SHIPPED' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $counts['SHIPPED'] }}</span>
            </a>
            <a href="{{ route('admin.orders', ['status' => 'DELIVERED']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0 {{ $statusFilter === 'DELIVERED' ? 'bg-emerald-700 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                <span>Selesai</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $statusFilter === 'DELIVERED' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['DELIVERED'] }}</span>
            </a>
        </div>

        <!-- ORDERS TABLE -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <tr>
                            <th class="py-3.5 px-4">Invoice & Tanggal</th>
                            <th class="py-3.5 px-4">Pelanggan & Tujuan</th>
                            <th class="py-3.5 px-4">Rincian Barang</th>
                            <th class="py-3.5 px-4">Metode Bayar</th>
                            <th class="py-3.5 px-4">Total</th>
                            <th class="py-3.5 px-4">Status & Kurir</th>
                            <th class="py-3.5 px-4 text-center">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50/80 transition">
                                <!-- Invoice & Date -->
                                <td class="py-4 px-4 align-top">
                                    <span class="font-mono font-bold text-gray-900 block">{{ $order->invoice_no }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                                    <a href="{{ route('orders.track', $order->id) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-brand-600 hover:underline mt-1 font-semibold">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        <span>Live Tracking</span>
                                    </a>
                                </td>

                                <!-- Customer & Delivery Address -->
                                <td class="py-4 px-4 align-top max-w-xs">
                                    <div class="font-bold text-gray-900">{{ $order->customer?->name ?? 'Pelanggan' }}</div>
                                    <div class="text-[11px] text-gray-500 flex items-center gap-1">
                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                        <span>{{ $order->customer?->phone }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-600 mt-1 line-clamp-2" title="{{ $order->delivery_address }}">
                                        {{ $order->delivery_address }}
                                    </div>
                                    @if($order->notes)
                                        <div class="mt-1 text-[10px] text-amber-700 bg-amber-50 p-1 rounded font-medium">
                                            Catatan: {{ $order->notes }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Items -->
                                <td class="py-4 px-4 align-top max-w-xs">
                                    <div class="space-y-1">
                                        @foreach($order->items as $it)
                                            <div class="text-[11px] text-gray-700 flex justify-between gap-2">
                                                <span class="truncate">{{ $it->product?->title ?? 'Item' }}</span>
                                                <span class="font-bold whitespace-nowrap">{{ $it->quantity }}x</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Payment Method & Status -->
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <span class="font-bold text-gray-900 block">{{ $order->payment_method }}</span>
                                    <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $order->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>

                                <!-- Total Price -->
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <div class="font-extrabold text-brand-600 text-sm">{{ $order->formatted_total }}</div>
                                    <div class="text-[10px] text-gray-400">Ongkir: {{ $order->formatted_shipping_fee }}</div>
                                </td>

                                <!-- Status & Assigned Courier -->
                                <td class="py-4 px-4 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm
                                        @if($order->order_status === 'PENDING') bg-amber-50 text-amber-700 border border-amber-200
                                        @elseif($order->order_status === 'CONFIRMED') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($order->order_status === 'PROCESSING') bg-indigo-50 text-indigo-700 border border-indigo-200
                                        @elseif($order->order_status === 'SHIPPED') bg-brand-50 text-brand-700 border border-brand-200 animate-subtle
                                        @elseif($order->order_status === 'DELIVERED') bg-emerald-100 text-emerald-800
                                        @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                                        {{ $order->status_label }}
                                    </span>

                                    <div class="mt-1 text-[11px] text-gray-500">
                                        Kurir: <strong class="text-gray-800">{{ $order->courier?->name ?? '-' }}</strong>
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 px-4 align-top text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        
                                        <!-- Terima Pesanan (PENDING -> CONFIRMED) -->
                                        @if($order->order_status === 'PENDING')
                                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="order_status" value="CONFIRMED">
                                                <button type="submit" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                                    Konfirmasi
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Kemas Pesanan (CONFIRMED -> PROCESSING) -->
                                        @if($order->order_status === 'CONFIRMED')
                                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="order_status" value="PROCESSING">
                                                <button type="submit" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                                    Kemas Barang
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Dispatch Courier Button (PROCESSING -> SHIPPED) -->
                                        @if(in_array($order->order_status, ['CONFIRMED', 'PROCESSING']))
                                            <button 
                                                @click="openAssignModal('{{ $order->id }}', '{{ $order->invoice_no }}')"
                                                class="px-2.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg font-bold text-xs shadow-sm transition flex items-center gap-1">
                                                <i data-lucide="bike" class="w-3.5 h-3.5"></i>
                                                <span>Tugaskan Kurir</span>
                                            </button>
                                        @endif

                                        <!-- Selesaikan Pesanan (SHIPPED -> DELIVERED) -->
                                        @if($order->order_status === 'SHIPPED')
                                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="order_status" value="DELIVERED">
                                                <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                                    Selesaikan
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Thermal Receipt Button -->
                                        <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold text-xs transition border flex items-center gap-1">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>Struk</span>
                                        </a>

                                        <!-- Cancel Order (if not delivered) -->
                                        @if(!in_array($order->order_status, ['DELIVERED', 'CANCELLED']))
                                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                                @csrf
                                                <input type="hidden" name="order_status" value="CANCELLED">
                                                <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition" title="Batalkan Pesanan">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                    Tidak ada transaksi dalam status ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>

    </div>

    <!-- MODAL ASSIGN COURIER -->
    <div x-show="assignModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="assignModalOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl z-10">
                <div class="flex items-center justify-between pb-3 border-b">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-sm">Pilih Kurir Pengantaran</h3>
                        <p class="text-xs text-gray-500">Invoice: <span class="font-bold text-brand-600" x-text="targetInvoice"></span></p>
                    </div>
                    <button @click="assignModalOpen = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                <form :action="`/admin/orders/${targetOrderId}/assign`" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Daftar Kurir Toko yang Online:</label>
                        <div class="mt-2 space-y-2">
                            @forelse($onlineCouriers as $cr)
                                <label class="flex items-center justify-between p-3 border rounded-xl hover:bg-gray-50 cursor-pointer transition">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                            {{ substr($cr->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-900">{{ $cr->name }}</div>
                                            <div class="text-[11px] text-gray-400">{{ $cr->phone }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online
                                        </span>
                                        <input type="radio" name="courier_id" value="{{ $cr->id }}" required class="text-brand-600">
                                    </div>
                                </label>
                            @empty
                                <div class="p-3 bg-amber-50 text-amber-800 rounded-xl text-xs">
                                    Belum ada kurir yang Online. Buka aplikasi kurir dan aktifkan toggle Online terlebih dahulu.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-brand-600/30">
                            Tugaskan & Mulai Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function adminOrdersApp() {
    return {
        assignModalOpen: false,
        targetOrderId: '',
        targetInvoice: '',
        lastCheckTime: new Date().toISOString().slice(0, 19).replace('T', ' '),

        init() {
            // Polling every 5 seconds for new orders & trigger sound alert!
            setInterval(() => {
                fetch(`/admin/api/live-orders?last_time=${encodeURIComponent(this.lastCheckTime)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.new_orders_count > 0) {
                            window.playAlertSound('order');
                            // Update timestamp and optionally reload or show banner
                            this.lastCheckTime = data.server_time;
                        }
                    })
                    .catch(err => console.error(err));
            }, 5000);
        },

        openAssignModal(orderId, invoice) {
            this.targetOrderId = orderId;
            this.targetInvoice = invoice;
            this.assignModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        playTestSound() {
            window.playAlertSound('order');
        }
    };
}
</script>
@endpush

