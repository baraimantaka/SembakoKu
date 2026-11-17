@extends('layouts.app')

@section('title', 'Live Tracking Pesanan ' . $order->invoice_no . ' - Toko Sembako Modern')

@section('content')
<div x-data="trackingApp()" x-init="init()" class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- HEADER & INVOICE BAR -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    <div>
                        <div class="text-xs font-semibold text-gray-500">Live Tracking Pesanan</div>
                        <h1 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight" x-text="order.invoice_no">{{ $order->invoice_no }}</h1>
                    </div>
                </div>
            </div>

            <!-- Current Status Pill -->
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold shadow-sm"
                    :class="{
                        'bg-amber-50 text-amber-700 border border-amber-200': order.order_status === 'PENDING',
                        'bg-blue-50 text-blue-700 border border-blue-200': order.order_status === 'CONFIRMED',
                        'bg-indigo-50 text-indigo-700 border border-indigo-200': order.order_status === 'PROCESSING',
                        'bg-brand-50 text-brand-700 border border-brand-200 animate-subtle': order.order_status === 'SHIPPED',
                        'bg-emerald-100 text-emerald-800 border border-emerald-300': order.order_status === 'DELIVERED',
                        'bg-rose-50 text-rose-700 border border-rose-200': order.order_status === 'CANCELLED'
                    }">
                    <span class="w-2.5 h-2.5 rounded-full"
                        :class="{
                            'bg-amber-500': order.order_status === 'PENDING',
                            'bg-blue-500': order.order_status === 'CONFIRMED',
                            'bg-indigo-500': order.order_status === 'PROCESSING',
                            'bg-brand-500 animate-ping': order.order_status === 'SHIPPED',
                            'bg-emerald-500': order.order_status === 'DELIVERED',
                            'bg-rose-500': order.order_status === 'CANCELLED'
                        }"></span>
                    <span x-text="order.status_label">{{ $order->status_label }}</span>
                </span>

                <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold transition flex items-center gap-1.5 border border-gray-200 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Struk</span>
                </a>
            </div>
        </div>

        <!-- 1. TIMELINE STEPPER (5 VISUAL STEPS) -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Status Progres Pengiriman</h2>
            
            <div class="relative">
                <!-- Progress Line -->
                <div class="absolute top-4 left-4 right-4 h-1 bg-gray-200 -z-0">
                    <div class="h-1 bg-brand-600 transition-all duration-700" 
                        :style="`width: ${Math.max(0, (order.status_step_index - 1) * 25)}%`"></div>
                </div>

                <!-- Steps Grid -->
                <div class="grid grid-cols-5 gap-1 relative z-10 text-center">
                    
                    <!-- Step 1: PENDING -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition duration-300 shadow"
                            :class="order.status_step_index >= 1 ? 'bg-brand-600 text-white ring-4 ring-brand-100' : 'bg-gray-200 text-gray-500'">
                            <template x-if="order.status_step_index > 1"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="order.status_step_index <= 1"><span>1</span></template>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold mt-2 line-clamp-1"
                            :class="order.status_step_index >= 1 ? 'text-gray-900 font-bold' : 'text-gray-400'">
                            Menunggu Bayar
                        </span>
                    </div>

                    <!-- Step 2: CONFIRMED -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition duration-300 shadow"
                            :class="order.status_step_index >= 2 ? 'bg-brand-600 text-white ring-4 ring-brand-100' : 'bg-gray-200 text-gray-500'">
                            <template x-if="order.status_step_index > 2"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="order.status_step_index <= 2"><span>2</span></template>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold mt-2 line-clamp-1"
                            :class="order.status_step_index >= 2 ? 'text-gray-900 font-bold' : 'text-gray-400'">
                            Dikonfirmasi
                        </span>
                    </div>

                    <!-- Step 3: PROCESSING -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition duration-300 shadow"
                            :class="order.status_step_index >= 3 ? 'bg-brand-600 text-white ring-4 ring-brand-100' : 'bg-gray-200 text-gray-500'">
                            <template x-if="order.status_step_index > 3"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="order.status_step_index <= 3"><span>3</span></template>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold mt-2 line-clamp-1"
                            :class="order.status_step_index >= 3 ? 'text-gray-900 font-bold' : 'text-gray-400'">
                            Sedang Dikemas
                        </span>
                    </div>

                    <!-- Step 4: SHIPPED -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition duration-300 shadow"
                            :class="order.status_step_index >= 4 ? 'bg-brand-600 text-white ring-4 ring-brand-100 animate-pulse' : 'bg-gray-200 text-gray-500'">
                            <template x-if="order.status_step_index > 4"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="order.status_step_index <= 4"><i data-lucide="bike" class="w-4 h-4"></i></template>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold mt-2 line-clamp-1"
                            :class="order.status_step_index >= 4 ? 'text-gray-900 font-bold text-brand-600' : 'text-gray-400'">
                            Pengantaran
                        </span>
                    </div>

                    <!-- Step 5: DELIVERED -->
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition duration-300 shadow"
                            :class="order.status_step_index >= 5 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-gray-200 text-gray-500'">
                            <i data-lucide="package-check" class="w-4 h-4"></i>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold mt-2 line-clamp-1"
                            :class="order.status_step_index >= 5 ? 'text-emerald-700 font-bold' : 'text-gray-400'">
                            Selesai
                        </span>
                    </div>

                </div>
            </div>
        </div>

        <!-- QRIS PENDING PAYMENT ALERT & SIMULATION (IF QRIS NOT YET PAID) -->
        <template x-if="order.payment_method === 'QRIS' && order.payment_status === 'PENDING'">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-3xl p-5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="bg-white p-2.5 rounded-2xl shadow-md flex-shrink-0">
                        <!-- QR Code Mockup -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=MIDTRANS_QRIS_{{ $order->invoice_no }}" alt="QRIS" class="w-20 h-20">
                    </div>
                    <div>
                        <div class="inline-block bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full mb-1">
                            Menunggu Pembayaran QRIS
                        </div>
                        <h3 class="font-extrabold text-base">Silakan Pindai QRIS untuk Melanjutkan</h3>
                        <p class="text-xs text-amber-100 mt-0.5">
                            Total Tagihan: <strong class="text-white text-sm" x-text="formatRupiah(order.total_price)"></strong>
                        </p>
                    </div>
                </div>

                <!-- One Click Simulator for Testing Convenience -->
                <button 
                    @click="simulatePayment()"
                    class="px-5 py-2.5 bg-white text-gray-900 hover:bg-amber-50 font-bold text-xs rounded-xl shadow-lg transition active:scale-95 flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>Simulasi Bayar QRIS Instan</span>
                </button>
            </div>
        </template>

        <!-- 2. LIVE INTERACTIVE TRACKING MAP (LEAFLET.JS) -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-xl bg-brand-50 text-brand-600">
                        <i data-lucide="map" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-sm">Peta Live Tracking Pergerakan Kurir</h3>
                        <p class="text-[11px] text-gray-500">Mendengarkan event broadcast lokasi GPS setiap 3 detik</p>
                    </div>
                </div>
                
                <span class="inline-flex items-center gap-1.5 text-[11px] text-brand-700 bg-brand-50 px-2.5 py-1 rounded-full font-bold">
                    <span class="w-2 h-2 rounded-full bg-brand-600 animate-ping"></span>
                    <span>Live GPS Active</span>
                </span>
            </div>

            <!-- Leaflet Map Container -->
            <div class="relative h-80 sm:h-96 w-full bg-gray-100">
                <div id="liveTrackingMap" class="w-full h-full"></div>

                <!-- Floating info bar on map -->
                <div class="absolute bottom-3 inset-x-3 sm:inset-x-auto sm:left-3 z-[400] bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-xl border border-gray-100 flex items-center gap-3 text-xs">
                    <div class="p-2 rounded-xl bg-brand-600 text-white">
                        <i data-lucide="navigation" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400">Update Lokasi Terakhir:</div>
                        <div class="font-bold text-gray-800" x-text="order.courier_location ? order.courier_location.updated_at : 'Menghubungkan...'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DRIVER / COURIER CARD & DESTINATION INFO -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- Courier Profile Card -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Kurir Pengantar</h3>
                
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-black text-lg border-2 border-brand-200 flex-shrink-0">
                        <i data-lucide="bike" class="w-7 h-7"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-base" x-text="order.courier ? order.courier.name : 'Menunggu Penugasan Kurir...'">
                            {{ $order->courier?->name ?? 'Belum Ditugaskan' }}
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Kurir Resmi Toko Sembako Modern</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i>
                                <span>4.9 (500+ Antaran)</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-2">
                    <template x-if="order.courier && order.courier.phone">
                        <a :href="`https://wa.me/${order.courier.phone.replace(/[^0-9]/g, '')}`" target="_blank" class="flex-1 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 border border-emerald-200">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Hubungi via WhatsApp</span>
                        </a>
                    </template>
                    <template x-if="!order.courier">
                        <div class="w-full text-center text-xs text-gray-400 py-1">
                            Kurir akan muncul setelah admin mengonfirmasi pesanan.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Destination Card -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tujuan Pengiriman</h3>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-xs font-bold text-gray-900">{{ $order->customer?->name ?? 'Pelanggan' }} ({{ $order->customer?->phone }})</div>
                            <div class="text-xs text-gray-600 mt-0.5">{{ $order->delivery_address }}</div>
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="mt-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-xs text-gray-600">
                            <strong>Catatan:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Metode: <strong class="text-gray-900">{{ $order->payment_method }}</strong></span>
                    <span>Status Bayar: 
                        <strong class="{{ $order->payment_status === 'PAID' ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $order->payment_status }}
                        </strong>
                    </span>
                </div>
            </div>

        </div>

        <!-- 4. ORDER ITEMS BREAKDOWN -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Rincian Barang yang Dipesan</h3>
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->product?->image_url }}" class="w-10 h-10 object-cover rounded-xl border flex-shrink-0">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $item->product?->title ?? 'Produk Sembako' }}</h4>
                                <div class="text-[11px] text-gray-500">
                                    {{ $item->quantity }} x {{ $item->formatted_price }}
                                </div>
                            </div>
                        </div>
                        <div class="font-extrabold text-gray-900">{{ $item->formatted_subtotal }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Total Price Summary -->
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900">{{ $order->formatted_subtotal }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Ongkos Kirim</span>
                    <span class="font-semibold text-gray-900">{{ $order->formatted_shipping_fee }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100 flex justify-between text-sm font-extrabold text-gray-900">
                    <span>Total Pembayaran</span>
                    <span class="text-brand-600 text-base">{{ $order->formatted_total }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function trackingApp() {
    return {
        order: {
            id: '{{ $order->id }}',
            invoice_no: '{{ $order->invoice_no }}',
            order_status: '{{ $order->order_status }}',
            status_label: '{{ $order->status_label }}',
            status_step_index: {{ $order->status_step_index }},
            payment_status: '{{ $order->payment_status }}',
            payment_method: '{{ $order->payment_method }}',
            total_price: {{ $order->total_price }},
            courier: @json($order->courier ? ['name' => $order->courier->name, 'phone' => $order->courier->phone] : null),
            courier_location: {
                latitude: {{ (float) $courierLat }},
                longitude: {{ (float) $courierLng }},
                updated_at: 'Terkini'
            }
        },

        storeLat: {{ (float) $storeLat }},
        storeLng: {{ (float) $storeLng }},
        destLat: {{ (float) $order->delivery_lat }},
        destLng: {{ (float) $order->delivery_lng }},

        mapInstance: null,
        courierMarker: null,
        routePolyline: null,

        init() {
            this.initMap();
            this.startPolling();
        },

        initMap() {
            this.mapInstance = L.map('liveTrackingMap').setView([this.order.courier_location.latitude, this.order.courier_location.longitude], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(this.mapInstance);

            // Store Marker (Emerald)
            const storeIcon = L.divIcon({
                className: 'store-icon',
                html: '<div class="bg-brand-600 text-white p-2 rounded-full shadow-xl border-2 border-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>'
            });
            L.marker([this.storeLat, this.storeLng], { icon: storeIcon })
                .addTo(this.mapInstance)
                .bindPopup('<strong>Toko Sembako Modern</strong><br>Titik Awal Pengiriman');

            // Destination Marker (Customer Home)
            const destIcon = L.divIcon({
                className: 'dest-icon',
                html: '<div class="bg-rose-500 text-white p-2 rounded-full shadow-xl border-2 border-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>'
            });
            L.marker([this.destLat, this.destLng], { icon: destIcon })
                .addTo(this.mapInstance)
                .bindPopup('<strong>Alamat Tujuan</strong><br>{{ $order->customer?->name }}');

            // Courier Marker (Moving Bike)
            const courierIcon = L.divIcon({
                className: 'courier-icon',
                html: '<div class="bg-amber-500 text-gray-950 p-2.5 rounded-full shadow-2xl border-2 border-white animate-bounce"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"/></svg></div>'
            });
            this.courierMarker = L.marker([this.order.courier_location.latitude, this.order.courier_location.longitude], { icon: courierIcon })
                .addTo(this.mapInstance)
                .bindPopup('<strong>Kurir SembakoKu</strong><br>Sedang bergerak');

            // Polyline connecting Store -> Courier -> Customer
            this.routePolyline = L.polyline([
                [this.storeLat, this.storeLng],
                [this.order.courier_location.latitude, this.order.courier_location.longitude],
                [this.destLat, this.destLng]
            ], { color: '#059669', weight: 4, dashArray: '6, 8', opacity: 0.8 }).addTo(this.mapInstance);

            // Fit bounds to include all three points
            this.mapInstance.fitBounds([
                [this.storeLat, this.storeLng],
                [this.order.courier_location.latitude, this.order.courier_location.longitude],
                [this.destLat, this.destLng]
            ], { padding: [50, 50] });
        },

        startPolling() {
            // Real-time polling fallback every 3 seconds for instant location & status synchronization
            setInterval(() => {
                fetch(`/api/orders/${this.order.id}/status`)
                    .then(res => res.json())
                    .then(data => {
                        this.order.order_status = data.order_status;
                        this.order.status_label = data.status_label;
                        this.order.status_step_index = data.status_step_index;
                        this.order.payment_status = data.payment_status;
                        if (data.courier) this.order.courier = data.courier;

                        if (data.courier_location) {
                            this.order.courier_location = data.courier_location;
                            const newLat = data.courier_location.latitude;
                            const newLng = data.courier_location.longitude;

                            if (this.courierMarker) {
                                this.courierMarker.setLatLng([newLat, newLng]);
                            }

                            if (this.routePolyline) {
                                this.routePolyline.setLatLngs([
                                    [this.storeLat, this.storeLng],
                                    [newLat, newLng],
                                    [this.destLat, this.destLng]
                                ]);
                            }
                        }
                    })
                    .catch(err => console.error('Tracking poll err', err));
            }, 3000);
        },

        simulatePayment() {
            fetch(`/orders/${this.order.id}/simulate-qris-pay`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                this.order.payment_status = 'PAID';
                this.order.order_status = 'CONFIRMED';
                this.order.status_step_index = 2;
                this.order.status_label = 'Pesanan Dikonfirmasi';
                window.playAlertSound('order');
            });
        },

        formatRupiah(amount) {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush

