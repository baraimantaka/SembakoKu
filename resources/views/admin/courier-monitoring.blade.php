@extends('layouts.app')

@section('title', 'Peta Multi-Kurir & Monitoring Delivery - Toko Sembako Modern')

@section('content')
<div x-data="courierMonitoringApp()" x-init="init()" class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-lg font-extrabold text-gray-900 tracking-tight">Peta Multi-Kurir & Monitoring Delivery</h1>
                    <p class="text-xs text-gray-500">Pantau seluruh armada kurir toko aktif & rute pengantaran secara real-time</p>
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
                <a href="{{ route('admin.couriers') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white shadow-sm flex items-center gap-1.5 flex-shrink-0">
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

        <!-- MAIN LAYOUT: MAP & COURIER SIDEBAR -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- MAP CONTAINER (3 COLS) -->
            <div class="lg:col-span-3 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-brand-600"></i>
                        <span>Radar Armada Tebet & Jakarta Selatan</span>
                    </span>
                    <span class="text-[11px] text-gray-400">Pusat: Toko Sembako Modern</span>
                </div>
                <div class="h-[550px] w-full relative">
                    <div id="multiCourierMap" class="w-full h-full"></div>
                </div>
            </div>

            <!-- COURIER LIST SIDEBAR (1 COL) -->
            <div class="space-y-4">
                <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Armada Kurir Toko ({{ $couriers->count() }})</h3>
                    
                    <div class="space-y-3">
                        @foreach($couriers as $cr)
                            <div class="p-3.5 rounded-2xl border transition {{ $cr->is_online ? 'border-brand-200 bg-brand-50/30' : 'border-gray-200 bg-gray-50' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs {{ $cr->is_online ? 'bg-brand-600 text-white' : 'bg-gray-300 text-gray-700' }}">
                                            <i data-lucide="bike" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-xs">{{ $cr->name }}</h4>
                                            <div class="text-[11px] text-gray-500">{{ $cr->phone }}</div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $cr->is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $cr->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </div>

                                <!-- Active orders assigned -->
                                <div class="mt-2.5 pt-2 border-t border-gray-200/60 text-[11px]">
                                    @if($cr->courierOrders->count() > 0)
                                        <div class="text-brand-700 font-semibold flex items-center gap-1">
                                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                            <span>Membawa {{ $cr->courierOrders->count() }} Pesanan Aktif</span>
                                        </div>
                                        @foreach($cr->courierOrders as $ord)
                                            <div class="text-[10px] text-gray-600 mt-1 pl-4 truncate">
                                                • {{ $ord->invoice_no }} ({{ $ord->customer?->name }})
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">Tidak ada pengantaran aktif saat ini.</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-gradient-to-br from-brand-600 to-emerald-800 text-white rounded-3xl p-5 shadow-lg">
                    <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                        <i data-lucide="radio" class="w-4 h-4 text-accent-400"></i>
                        <span>Reverb WebSocket Engine</span>
                    </div>
                    <p class="text-xs text-brand-100 leading-relaxed">
                        Saat kurir menekan "Mulai Pengantaran", koordinat GPS dari smartphone driver dibroadcast secara live via event <code class="bg-black/30 px-1 py-0.5 rounded text-accent-300">CourierLocationUpdated</code>.
                    </p>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function courierMonitoringApp() {
    return {
        couriers: @json($couriers),
        storeLat: {{ (float) $storeLat }},
        storeLng: {{ (float) $storeLng }},
        storeName: '{{ $storeName }}',
        mapInstance: null,

        init() {
            this.initMap();
        },

        initMap() {
            this.mapInstance = L.map('multiCourierMap').setView([this.storeLat, this.storeLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(this.mapInstance);

            // Store Base Marker
            const storeIcon = L.divIcon({
                className: 'store-icon',
                html: '<div class="bg-brand-600 text-white p-2.5 rounded-full shadow-2xl border-2 border-white"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg></div>'
            });
            L.marker([this.storeLat, this.storeLng], { icon: storeIcon })
                .addTo(this.mapInstance)
                .bindPopup(`<strong>${this.storeName}</strong><br>Pusat Logistik & Gudang Toko`);

            // Couriers Markers
            this.couriers.forEach(cr => {
                const lat = cr.latitude || this.storeLat;
                const lng = cr.longitude || this.storeLng;

                const courierIcon = L.divIcon({
                    className: 'courier-pin',
                    html: `<div class="${cr.is_online ? 'bg-amber-500 text-gray-950 animate-bounce' : 'bg-gray-400 text-white'} p-2 rounded-full shadow-xl border-2 border-white"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"/></svg></div>`
                });

                L.marker([lat, lng], { icon: courierIcon })
                    .addTo(this.mapInstance)
                    .bindPopup(`<strong>${cr.name}</strong><br>Status: ${cr.is_online ? 'Online' : 'Offline'}<br>Telp: ${cr.phone}`);
            });
        }
    };
}
</script>
@endpush

