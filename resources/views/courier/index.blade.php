@extends('layouts.app')

@section('title', 'Aplikasi Driver Kurir Sembako - Toko Sembako Modern')

@section('content')
<div x-data="courierApp()" x-init="init()" class="min-h-screen bg-gray-100 pb-16">
    
    <!-- DRIVER MOBILE HEADER -->
    <div class="bg-gray-900 text-white px-4 pt-5 pb-8 shadow-lg">
        <div class="max-w-md mx-auto">
            
            <!-- Top bar: Courier profile & Status Toggle -->
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500 text-gray-950 flex items-center justify-center font-black text-base shadow-md">
                        <i data-lucide="bike" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h1 class="font-extrabold text-sm text-white" x-text="courier.name">{{ $courier->name }}</h1>
                            <!-- Switch courier profile dropdown -->
                            <select @change="window.location.href = `/courier?courier_id=${$event.target.value}`" class="bg-gray-800 text-[10px] text-gray-300 rounded px-1.5 py-0.5 border border-gray-700 outline-none">
                                @foreach($allCouriers as $crOption)
                                    <option value="{{ $crOption->id }}" {{ $crOption->id === $courier->id ? 'selected' : '' }}>
                                        Ganti: {{ $crOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-[11px] text-gray-400">{{ $courier->phone }}</div>
                    </div>
                </div>

                <!-- Online / Offline Toggle Button -->
                <button 
                    @click="toggleOnlineStatus()"
                    :class="courier.is_online ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-gray-700 text-gray-300'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                    <span class="w-2 h-2 rounded-full" :class="courier.is_online ? 'bg-white animate-pulse' : 'bg-gray-400'"></span>
                    <span x-text="courier.is_online ? 'ONLINE' : 'OFFLINE'">{{ $courier->is_online ? 'ONLINE' : 'OFFLINE' }}</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 gap-3 mt-5 bg-gray-800/80 rounded-2xl p-3 border border-gray-700/60 text-xs">
                <div>
                    <div class="text-[10px] text-gray-400">Total Selesai Antar:</div>
                    <div class="text-base font-bold text-white">{{ $completedTasks->count() }} Pengantaran</div>
                </div>
                <div>
                    <div class="text-[10px] text-gray-400">Rating Performa:</div>
                    <div class="text-base font-bold text-amber-400 flex items-center gap-1">
                        <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                        <span>4.9 / 5.0</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MAIN DRIVER CONTAINER (MOBILE ORIENTED) -->
    <div class="max-w-md mx-auto px-4 -mt-4 space-y-4">

        <!-- 1. ACTIVE ASSIGNED TASK (IF ANY) -->
        @if($activeTask)
            <div class="bg-white rounded-3xl p-5 shadow-xl border border-gray-200 space-y-4">
                
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                        {{ $activeTask->order_status === 'SHIPPED' ? 'bg-brand-50 text-brand-700 border border-brand-200 animate-subtle' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                        {{ $activeTask->order_status === 'SHIPPED' ? 'Sedang Diantar (Live GPS)' : 'Tugas Baru Diterima' }}
                    </span>
                    <span class="text-xs font-mono font-bold text-gray-500">{{ $activeTask->invoice_no }}</span>
                </div>

                <!-- Destination & Customer Info -->
                <div class="bg-gray-50 rounded-2xl p-3.5 border border-gray-200/80">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-xs font-bold text-gray-900">{{ $activeTask->customer?->name }} ({{ $activeTask->customer?->phone }})</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $activeTask->delivery_address }}</div>
                            @if($activeTask->notes)
                                <div class="mt-2 text-[11px] text-amber-800 bg-amber-50 p-1.5 rounded-lg border border-amber-200 font-medium">
                                    Catatan: {{ $activeTask->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Status Pill (COD vs QRIS) -->
                <div class="p-3.5 rounded-2xl flex items-center justify-between text-xs font-bold
                    {{ $activeTask->payment_method === 'COD' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                    <div class="flex items-center gap-2">
                        <i data-lucide="{{ $activeTask->payment_method === 'COD' ? 'banknote' : 'qr-code' }}" class="w-5 h-5"></i>
                        <div>
                            <div class="text-[10px] uppercase tracking-wider">{{ $activeTask->payment_method === 'COD' ? 'Instruksi Kasir COD:' : 'Metode QRIS:' }}</div>
                            <div class="text-sm font-black">
                                @if($activeTask->payment_method === 'COD')
                                    TAGIH COD: Rp {{ number_format($activeTask->total_price, 0, ',', '.') }}
                                @else
                                    LUNAS (QRIS) - JANGAN TAGIH UANG
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ordered Items Quick Summary -->
                <div class="border-t border-gray-100 pt-3">
                    <div class="text-[11px] font-bold text-gray-500 uppercase mb-1.5">Barang yang Diantar:</div>
                    <div class="space-y-1 text-xs">
                        @foreach($activeTask->items as $it)
                            <div class="flex justify-between text-gray-700">
                                <span>{{ $it->product?->title }}</span>
                                <span class="font-bold">{{ $it->quantity }}x</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation Quick Button (Google Maps / Waze) -->
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <a 
                        href="https://www.google.com/maps/dir/?api=1&destination={{ $activeTask->delivery_lat }},{{ $activeTask->delivery_lng }}" 
                        target="_blank" 
                        class="py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md">
                        <i data-lucide="navigation" class="w-4 h-4"></i>
                        <span>Buka Google Maps</span>
                    </a>
                    
                    <a 
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $activeTask->customer?->phone ?? '') }}" 
                        target="_blank" 
                        class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Chat Pelanggan</span>
                    </a>
                </div>

                <!-- 2. REALTIME GPS BROADCASTER SWITCH -->
                <div class="p-3.5 bg-gray-900 text-white rounded-2xl space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="isBroadcasting ? 'bg-emerald-400 animate-ping' : 'bg-gray-500'"></span>
                            <span class="text-xs font-bold">GPS Broadcaster (3-5 detik)</span>
                        </div>
                        <button 
                            @click="toggleGpsBroadcasting('{{ $activeTask->id }}')" 
                            :class="isBroadcasting ? 'bg-rose-500 text-white' : 'bg-brand-500 text-white'"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase transition">
                            <span x-text="isBroadcasting ? 'Stop GPS' : 'Mulai Kirim GPS'"></span>
                        </button>
                    </div>
                    <div class="text-[11px] text-gray-300">
                        Status: <span class="font-bold text-accent-400" x-text="broadcasterStatusText">Siap diaktifkan</span>
                    </div>
                    <div class="text-[10px] text-gray-400">
                        Koordinat Terakhir: <span x-text="`${currentLat.toFixed(5)}, ${currentLng.toFixed(5)}`"></span>
                    </div>
                </div>

                <!-- 3. ACTIONS: MULAI PENGANTARAN / SELESAI (POD PHOTO) -->
                <div class="pt-2">
                    @if($activeTask->order_status !== 'SHIPPED')
                        <button 
                            @click="startDelivery('{{ $activeTask->id }}')"
                            class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl font-black text-sm shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-95">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Mulai Pengantaran (Aktifkan GPS)</span>
                        </button>
                    @else
                        <button 
                            @click="openPodModal = true"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-sm shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-2 transition active:scale-95">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Selesaikan & Foto Bukti Penerimaan (POD)</span>
                        </button>
                    @endif
                </div>

            </div>
        @else
            <!-- No active delivery task -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-3">
                    <i data-lucide="check-check" class="w-8 h-8 text-emerald-500"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-sm">Tidak Ada Tugas Aktif</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">
                    Status Anda sedang stand-by. Begitu admin toko menugaskan pesanan, detailnya akan muncul di sini.
                </p>
            </div>
        @endif

        <!-- 4. AVAILABLE UNASSIGNED TASKS -->
        @if($availableTasks->count() > 0)
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="inbox" class="w-4 h-4 text-brand-600"></i>
                        <span>Tugas Pengantaran Tersedia ({{ $availableTasks->count() }})</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @foreach($availableTasks as $t)
                        <div class="p-3.5 rounded-2xl border border-gray-200 bg-gray-50/60 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-xs text-gray-800">{{ $t->invoice_no }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $t->payment_method === 'COD' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $t->payment_method }} • Rp {{ number_format($t->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 mt-1 line-clamp-1">
                                    Kirim ke: <strong>{{ $t->customer?->name }}</strong> - {{ $t->delivery_address }}
                                </div>
                            </div>

                            <form action="{{ route('courier.accept-task', $t->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="courier_id" value="{{ $courier->id }}">
                                <button type="submit" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Ambil Tugas Ini</span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 5. RECENT DELIVERIES HISTORY -->
        @if($completedTasks->count() > 0)
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-bold text-gray-900 text-xs uppercase tracking-wider">Riwayat Pengantaran Selesai</h3>
                <div class="divide-y divide-gray-100">
                    @foreach($completedTasks as $ct)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-gray-800">{{ $ct->invoice_no }}</div>
                                <div class="text-[11px] text-gray-400">{{ $ct->customer?->name }} • {{ $ct->created_at->format('d M H:i') }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[10px]">
                                SELESAI
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <!-- PROOF OF DELIVERY (POD) PHOTO MODAL -->
    @if($activeTask)
        <div x-show="openPodModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div @click="openPodModal = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

                <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl z-10">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="font-extrabold text-gray-900 text-sm">Upload Bukti Penerimaan (POD)</h3>
                            <p class="text-xs text-gray-500">Ambil foto sembako diterima pelanggan</p>
                        </div>
                        <button @click="openPodModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>

                    <form action="{{ route('courier.complete-delivery', $activeTask->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        
                        <!-- Upload Container / Camera Capture -->
                        <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-brand-500 transition cursor-pointer">
                            <i data-lucide="camera" class="w-10 h-10 mx-auto text-brand-600 mb-2"></i>
                            <div class="text-xs font-bold text-gray-700">Pilih atau Ambil Foto</div>
                            <div class="text-[11px] text-gray-400 mt-0.5">Mendukung Kamera Smartphone (JPEG, PNG)</div>
                            <input type="file" name="proof_image" accept="image/*" capture="environment" class="mt-3 block w-full text-xs text-gray-500">
                        </div>

                        <!-- Fallback URL / Preset demo photo option -->
                        <div>
                            <label class="text-[11px] text-gray-600 font-medium">Atau gunakan URL Foto Sampel Demo:</label>
                            <input type="url" name="proof_image_url" value="https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=600&q=80" class="w-full mt-1 p-2 border rounded-xl text-xs outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-600/30 transition">
                                Konfirmasi & Selesaikan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function courierApp() {
    return {
        courier: {
            id: '{{ $courier->id }}',
            name: '{{ $courier->name }}',
            phone: '{{ $courier->phone }}',
            is_online: {{ $courier->is_online ? 'true' : 'false' }},
        },
        currentLat: {{ (float) ($courier->latitude ?? -7.0070) }},
        currentLng: {{ (float) ($courier->longitude ?? 110.4365) }},
        
        targetLat: {{ $activeTask ? (float) $activeTask->delivery_lat : -7.0125 }},
        targetLng: {{ $activeTask ? (float) $activeTask->delivery_lng : 110.4320 }},

        isBroadcasting: {{ ($activeTask && $activeTask->order_status === 'SHIPPED') ? 'true' : 'false' }},
        broadcasterStatusText: '{{ ($activeTask && $activeTask->order_status === "SHIPPED") ? "Aktif mengirim koordinat GPS..." : "Stand-by" }}',
        timerId: null,
        openPodModal: false,

        init() {
            if (this.isBroadcasting) {
                this.startGpsLoop('{{ $activeTask?->id }}');
            }
        },

        toggleOnlineStatus() {
            fetch(`/courier/${this.courier.id}/toggle-online`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                this.courier.is_online = data.is_online;
            });
        },

        startDelivery(orderId) {
            fetch(`/courier/orders/${orderId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isBroadcasting = true;
                this.broadcasterStatusText = 'Aktif mengirim koordinat GPS...';
                this.startGpsLoop(orderId);
                window.location.reload();
            });
        },

        toggleGpsBroadcasting(orderId) {
            this.isBroadcasting = !this.isBroadcasting;
            if (this.isBroadcasting) {
                this.broadcasterStatusText = 'Aktif mengirim koordinat GPS...';
                this.startGpsLoop(orderId);
            } else {
                this.broadcasterStatusText = 'GPS Dihentikan sementara';
                if (this.timerId) clearInterval(this.timerId);
            }
        },

        startGpsLoop(orderId) {
            if (!orderId) return;

            // Broadcast every 3.5 seconds
            this.timerId = setInterval(() => {
                // If real GPS available in smartphone browser
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.currentLat = pos.coords.latitude;
                            this.currentLng = pos.coords.longitude;
                            this.sendLocation(orderId, this.currentLat, this.currentLng);
                        },
                        () => {
                            // Smart simulated step towards destination for desktop preview
                            this.simulateMovementStep();
                            this.sendLocation(orderId, this.currentLat, this.currentLng);
                        }
                    );
                } else {
                    this.simulateMovementStep();
                    this.sendLocation(orderId, this.currentLat, this.currentLng);
                }
            }, 3500);
        },

        simulateMovementStep() {
            // Nudge coordinates 5% closer to customer destination
            const dLat = (this.targetLat - this.currentLat) * 0.08;
            const dLng = (this.targetLng - this.currentLng) * 0.08;
            this.currentLat += dLat;
            this.currentLng += dLng;
        },

        sendLocation(orderId, lat, lng) {
            fetch(`/courier/orders/${orderId}/location`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    courier_id: this.courier.id
                })
            })
            .then(res => res.json())
            .then(data => {
                this.broadcasterStatusText = `Lokasi terkirim: ${new Date().toLocaleTimeString('id-ID')}`;
            })
            .catch(err => console.error('GPS broadcast err', err));
        }
    };
}
</script>
@endpush

