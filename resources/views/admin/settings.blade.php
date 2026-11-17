@extends('layouts.app')

@section('title', 'Pengaturan Toko & Lokasi GPS - Admin Toko Sembako')

@section('content')
<div x-data="storeSettingsApp()" x-init="init()" class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- TOP BAR & NAVIGATION -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-lg shadow-brand-600/30">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-gray-900 tracking-tight">Pengaturan Toko & Titik Lokasi GPS</h1>
                    <p class="text-xs text-gray-500">Atur koordinat pusat toko, jam operasional, tarif ongkir, dan status toko</p>
                </div>
            </div>

            <!-- Admin Sub Nav -->
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
                <a href="{{ route('admin.analytics') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Laporan & KPI</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white shadow-sm flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Pengaturan Toko</span>
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-xs text-rose-700">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 text-rose-600 mt-0.5"></i>
                <div>
                    <strong class="font-bold">Terjadi kesalahan pada formulir:</strong>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- MAIN GRID: FORM (LEFT) & INTERACTIVE MAP (RIGHT) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: SETTINGS FORM (5 COLS) -->
            <div class="lg:col-span-5 bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-brand-600"></i>
                        <span>Konfigurasi Operasional Toko</span>
                    </h2>

                    <form action="{{ route('admin.settings.update') }}" method="POST" id="storeSettingForm" class="space-y-4 text-xs">
                        @csrf

                        <!-- Store Name -->
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Nama Toko Sembako</label>
                            <input 
                                type="text" 
                                name="store_name" 
                                value="{{ old('store_name', $settings->store_name) }}" 
                                required 
                                class="w-full p-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition text-xs font-semibold">
                        </div>

                        <!-- Store Coordinates (Auto-filled by map) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1 flex items-center gap-1">
                                    <span>Latitude</span>
                                    <span class="text-[10px] text-brand-600 font-normal">(Auto)</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="store_latitude" 
                                    id="inputLat" 
                                    x-model="lat" 
                                    required 
                                    readonly
                                    class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none text-xs font-mono font-bold text-gray-800">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 mb-1 flex items-center gap-1">
                                    <span>Longitude</span>
                                    <span class="text-[10px] text-brand-600 font-normal">(Auto)</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="store_longitude" 
                                    id="inputLng" 
                                    x-model="lng" 
                                    required 
                                    readonly
                                    class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none text-xs font-mono font-bold text-gray-800">
                            </div>
                        </div>

                        <!-- Store Address (Auto reverse-geocoded) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-gray-700">Alamat Lengkap Toko</label>
                                <span x-show="isGeocoding" class="text-[10px] text-brand-600 font-semibold flex items-center gap-1" x-cloak>
                                    <div class="w-3 h-3 border-2 border-brand-600 border-t-transparent rounded-full animate-spin"></div>
                                    <span>Membaca alamat...</span>
                                </span>
                            </div>
                            <textarea 
                                name="store_address" 
                                id="inputAddress"
                                x-model="address" 
                                rows="3" 
                                required 
                                class="w-full p-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition text-xs leading-relaxed"></textarea>
                            <span class="text-[10px] text-gray-400 mt-1 block">
                                Alamat ini otomatis terisi saat pin di peta digeser atau Anda bisa mengetiknya manual.
                            </span>
                        </div>

                        <!-- Operational Hours -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Jam Buka Toko</label>
                                <input 
                                    type="time" 
                                    name="open_time" 
                                    value="{{ old('open_time', \Carbon\Carbon::parse($settings->open_time)->format('H:i')) }}" 
                                    required 
                                    class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Jam Tutup Toko</label>
                                <input 
                                    type="time" 
                                    name="close_time" 
                                    value="{{ old('close_time', \Carbon\Carbon::parse($settings->close_time)->format('H:i')) }}" 
                                    required 
                                    class="w-full p-2.5 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 text-xs bg-white">
                            </div>
                        </div>

                        <!-- Shipping Rate per KM -->
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tarif Ongkos Kirim per KM (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">Rp</span>
                                <input 
                                    type="number" 
                                    name="shipping_rate_per_km" 
                                    value="{{ old('shipping_rate_per_km', $settings->shipping_rate_per_km) }}" 
                                    min="500" 
                                    step="500" 
                                    required 
                                    class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-brand-500 font-bold text-gray-900 text-xs">
                            </div>
                            <span class="text-[10px] text-gray-400 mt-0.5 block">
                                Digunakan sistem untuk menghitung ongkir otomatis sesuai jarak rumah pelanggan.
                            </span>
                        </div>

                        <!-- Manual Store Open/Close Toggle -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-xs text-gray-800">Status Operasional Manual</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    Aktifkan jika toko buka, matikan jika toko tutup sementara / libur.
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="is_open" 
                                    value="1" 
                                    {{ old('is_open', $settings->is_open) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-3 border-t">
                            <button 
                                type="submit" 
                                class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 active:scale-98 text-white rounded-2xl font-bold text-xs sm:text-sm shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 transition">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Simpan Perubahan Toko</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: INTERACTIVE LEAFLET MAP (7 COLS) -->
            <div class="lg:col-span-7 bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm flex flex-col">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="map" class="w-4 h-4 text-brand-600"></i>
                            <span>Pin Titik Lokasi Toko (Peta Interaktif)</span>
                        </h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Geser pin merah atau klik pada peta untuk memindahkan titik pusat toko
                        </p>
                    </div>

                    <!-- GPS Auto Detect Button -->
                    <button 
                        type="button" 
                        @click="detectGPSLocation()" 
                        class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-brand-700 border border-brand-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm flex-shrink-0 active:scale-95">
                        <i data-lucide="crosshair" class="w-4 h-4 text-brand-600"></i>
                        <span>Gunakan Lokasi Saat Ini (GPS)</span>
                    </button>
                </div>

                <!-- Leaflet Map Canvas -->
                <div class="relative w-full h-[450px] sm:h-[500px] rounded-2xl overflow-hidden border border-gray-200">
                    <div id="storeSettingMap" class="w-full h-full"></div>

                    <!-- Floating Badge on Map -->
                    <div class="absolute bottom-3 left-3 z-[400] bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-xl border border-gray-200 text-xs max-w-xs sm:max-w-sm">
                        <div class="flex items-center gap-2 font-bold text-gray-900 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600 animate-ping"></span>
                            <span>Titik Pusat Toko Terpilih:</span>
                        </div>
                        <div class="text-[11px] font-mono text-gray-700">
                            Lat: <span x-text="lat" class="font-bold"></span> | Lng: <span x-text="lng" class="font-bold"></span>
                        </div>
                        <div class="text-[10px] text-gray-500 mt-1 line-clamp-2" x-text="address"></div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-brand-50/60 rounded-2xl border border-brand-200 flex items-start gap-2.5 text-xs text-brand-900">
                    <i data-lucide="info" class="w-4 h-4 text-brand-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <strong>Efek ke Pelanggan:</strong> Perubahan titik koordinat toko di halaman ini akan langsung mempengaruhi perhitungan jarak kilometer dan ongkir di aplikasi belanja pelanggan secara akurat tanpa perlu restart server.
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function storeSettingsApp() {
    return {
        lat: {{ (float) $settings->store_latitude }},
        lng: {{ (float) $settings->store_longitude }},
        address: @json($settings->store_address),
        isGeocoding: false,
        map: null,
        marker: null,

        init() {
            this.$nextTick(() => {
                this.initMap();
            });
        },

        initMap() {
            this.map = L.map('storeSettingMap').setView([this.lat, this.lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            const storeIcon = L.divIcon({
                className: 'custom-store-marker',
                html: `
                    <div class="relative flex items-center justify-center">
                        <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-2xl border-2 border-white animate-bounce">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });

            this.marker = L.marker([this.lat, this.lng], {
                draggable: true,
                icon: storeIcon
            }).addTo(this.map);

            this.marker.bindPopup('<strong>Titik Lokasi Toko Sembako</strong><br>Geser untuk mengubah titik').openPopup();

            // Drag event
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.updatePosition(pos.lat, pos.lng);
            });

            // Click event on map
            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.updatePosition(e.latlng.lat, e.latlng.lng);
            });
        },

        updatePosition(newLat, newLng) {
            this.lat = Number(newLat).toFixed(7);
            this.lng = Number(newLng).toFixed(7);

            // Reverse geocode via OpenStreetMap Nominatim
            this.reverseGeocode(newLat, newLng);
        },

        reverseGeocode(lat, lng) {
            this.isGeocoding = true;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        this.address = data.display_name;
                    }
                    this.isGeocoding = false;
                })
                .catch(err => {
                    this.isGeocoding = false;
                    console.warn('Reverse geocoding error', err);
                });
        },

        detectGPSLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;
                    this.map.setView([userLat, userLng], 17);
                    this.marker.setLatLng([userLat, userLng]);
                    this.updatePosition(userLat, userLng);
                },
                (err) => {
                    alert('Gagal mendeteksi lokasi GPS Anda: ' + err.message);
                },
                { enableHighAccuracy: true }
            );
        }
    };
}
</script>
@endpush
