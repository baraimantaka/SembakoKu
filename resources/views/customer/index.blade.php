@extends('layouts.app')

@section('title', 'Toko Sembako Modern - Delivery Instan GoMart Style')

@section('content')
<div x-data="customerApp()" x-init="init()" class="min-h-screen bg-gray-50 pb-28">

    <!-- HERO & STORE HEADER -->
    <div class="bg-gradient-to-b from-brand-600 via-brand-700 to-emerald-800 text-white pt-6 pb-12 px-4 shadow-lg">
        <div class="max-w-6xl mx-auto">
            <!-- Top Row: Address Selector & Store Hours -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-white/15">
                <!-- Location Picker -->
                <div class="flex items-start gap-2.5 cursor-pointer group" @click="openLocationModal = true">
                    <div class="p-2 rounded-xl bg-white/10 backdrop-blur text-accent-400 group-hover:bg-white/20 transition">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[11px] text-brand-100 flex items-center gap-1">
                            <span>Kirim ke Alamat:</span>
                            <span class="text-accent-300 font-semibold flex items-center">
                                Ganti <i data-lucide="chevron-down" class="w-3 h-3 ml-0.5"></i>
                            </span>
                        </div>
                        <div class="text-sm font-semibold truncate max-w-xs sm:max-w-md text-white" x-text="deliveryAddress">
                            {{ $customerProfile->address ?? 'Pilih Alamat Pengantaran' }}
                        </div>
                    </div>
                </div>

                <!-- Store Operational Badge -->
                <div class="flex items-center gap-3 self-start sm:self-center bg-black/20 backdrop-blur px-3.5 py-1.5 rounded-full border border-white/10 text-xs">
                    <span class="flex h-2.5 w-2.5 relative">
                        @if($storeStatus['is_open'])
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                        @else
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-400"></span>
                        @endif
                    </span>
                    <span class="font-medium {{ $storeStatus['is_open'] ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $storeStatus['label'] }}
                    </span>
                    <span class="text-white/40">|</span>
                    <span class="text-white/80 text-[11px]">{{ $storeStatus['schedule'] }}</span>
                </div>
            </div>

            <!-- Header Branding & Tagline -->
            <div class="mt-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-accent-500/20 text-accent-300 text-xs font-semibold mb-2">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Pengiriman Kilat 15-30 Menit Sampai</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Toko Sembako Modern</h1>
                    <p class="text-brand-100 text-sm mt-1 max-w-xl">
                        Pilihan bahan pokok lengkap, beras pulen, minyak, telur fresh, gas & galon siap antar langsung ke depan pintu rumah Anda.
                    </p>
                </div>

                <!-- Distance info badge -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/10 text-right hidden sm:block">
                    <div class="text-[11px] text-brand-100">Jarak dari Toko:</div>
                    <div class="text-lg font-bold text-white flex items-center justify-end gap-1">
                        <span x-text="distanceKm.toFixed(1)">{{ $initialShipping['distance_km'] }}</span> km
                    </div>
                    <div class="text-[11px] text-accent-300">
                        Ongkir: <span x-text="formatRupiah(shippingFee)">{{ 'Rp ' . number_format($initialShipping['fee'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- FLOATING LIVE SEARCH BAR -->
            <div class="mt-6 relative">
                <div class="relative flex items-center">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 text-gray-400 pointer-events-none"></i>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        @input.debounce.300ms="performLiveSearch()"
                        placeholder="Cari sembako... (misal: Beras, Bimoli, Telur, Gas 3kg, Sunlight)"
                        class="w-full pl-12 pr-10 py-3.5 rounded-2xl bg-white text-gray-900 shadow-xl placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-500/30 text-sm sm:text-base transition">
                    <button 
                        x-show="searchQuery" 
                        @click="searchQuery = ''; performLiveSearch()" 
                        class="absolute right-4 text-gray-400 hover:text-gray-600" x-cloak>
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="max-w-6xl mx-auto px-4 mt-6">

        <!-- CATEGORY FILTER PILLS (WITH ICONS) -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-1.5">
                    <i data-lucide="grid" class="w-4 h-4 text-brand-600"></i>
                    <span>Kategori Sembako</span>
                </h2>
                <span class="text-xs text-gray-500" x-text="`${products.length} produk ditemukan`"></span>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
                @foreach($categories as $cat => $icon)
                    <button 
                        type="button"
                        @click="selectCategory('{{ $cat }}')"
                        :class="selectedCategory === '{{ $cat }}' 
                            ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20 border-brand-600' 
                            : 'bg-white text-gray-700 hover:bg-gray-100 border-gray-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border whitespace-nowrap transition flex items-center gap-2 flex-shrink-0">
                        @if($cat === 'Semua')
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        @elseif($cat === 'Beras')
                            <i data-lucide="wheat" class="w-4 h-4"></i>
                        @elseif($cat === 'Minyak & Bumbu')
                            <i data-lucide="flame" class="w-4 h-4"></i>
                        @elseif($cat === 'Telur & Susu')
                            <i data-lucide="egg" class="w-4 h-4"></i>
                        @elseif($cat === 'Gas & Galon')
                            <i data-lucide="fuel" class="w-4 h-4"></i>
                        @elseif($cat === 'Makanan Ringan')
                            <i data-lucide="cookie" class="w-4 h-4"></i>
                        @elseif($cat === 'Kebersihan')
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        @endif
                        <span>{{ $cat }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <div>
            <!-- Loading indicator -->
            <div x-show="loading" class="py-16 text-center" x-cloak>
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-brand-500 border-t-transparent"></div>
                <p class="text-xs text-gray-500 mt-2">Memuat produk...</p>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && products.length === 0" class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-sm" x-cloak>
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
                    <i data-lucide="search-x" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800">Produk Tidak Ditemukan</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Maaf, tidak ada produk sembako yang cocok dengan pencarian "<span x-text="searchQuery"></span>".
                </p>
                <button @click="searchQuery = ''; selectedCategory = 'Semua'; performLiveSearch()" class="mt-4 px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-semibold hover:bg-brand-700 transition">
                    Reset Pencarian
                </button>
            </div>

            <!-- Grid Items -->
            <div x-show="!loading && products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <template x-for="product in products" :key="product.id">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden group">
                        
                        <!-- Image Container with Badges -->
                        <div class="relative aspect-square bg-gray-100 overflow-hidden">
                            <img :src="product.image_url" :alt="product.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            
                            <!-- Stock Status Badge -->
                            <div class="absolute top-2.5 left-2.5">
                                <span :class="product.stock_badge.class" class="text-[10px] font-bold px-2 py-0.5 rounded-full border shadow-sm" x-text="product.stock_badge.text"></span>
                            </div>

                            <!-- Sold Counter Badge -->
                            <div class="absolute bottom-2.5 left-2.5 bg-black/60 backdrop-blur-md text-white text-[10px] font-medium px-2 py-0.5 rounded-md flex items-center gap-1">
                                <i data-lucide="flame" class="w-3 h-3 text-amber-400"></i>
                                <span x-text="product.formatted_sold"></span>
                            </div>
                        </div>

                        <!-- Card Info Body -->
                        <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-gray-600 font-semibold" x-text="product.category"></span>
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 mt-0.5 line-clamp-2 leading-snug" x-text="product.title"></h3>
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs text-gray-600">Harga / <span x-text="product.unit"></span></div>
                                    <div class="text-sm sm:text-base font-extrabold text-brand-600" x-text="product.formatted_price"></div>
                                </div>

                                <!-- Add / Quantity Control Buttons -->
                                <div>
                                    <template x-if="product.stock <= 0">
                                        <button disabled class="px-3 py-1.5 bg-gray-100 text-gray-400 rounded-xl text-xs font-semibold cursor-not-allowed">
                                            Habis
                                        </button>
                                    </template>

                                    <template x-if="product.stock > 0">
                                        <div>
                                            <!-- If item is not in cart yet: Show '+ Tambah' -->
                                            <template x-if="!getCartItem(product.id)">
                                                <button 
                                                    @click="addToCart(product)"
                                                    class="px-3 py-1.5 bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1 border border-brand-200 shadow-sm active:scale-95">
                                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                    <span>Tambah</span>
                                                </button>
                                            </template>

                                            <!-- If item is in cart: Show [- Qty +] Counter -->
                                            <template x-if="getCartItem(product.id)">
                                                <div class="flex items-center bg-brand-600 text-white rounded-xl shadow-sm overflow-hidden p-0.5">
                                                    <button 
                                                        @click="decrementCart(product.id)" 
                                                        class="w-7 h-7 flex items-center justify-center hover:bg-brand-700 rounded-lg transition active:scale-90">
                                                        <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <span class="w-6 text-center text-xs font-extrabold" x-text="getCartItem(product.id).quantity"></span>
                                                    <button 
                                                        @click="incrementCart(product.id)" 
                                                        class="w-7 h-7 flex items-center justify-center hover:bg-brand-700 rounded-lg transition active:scale-90">
                                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- FLOATING CART BAR (STICKY AT BOTTOM) -->
    <div 
        x-show="cartTotalItems > 0" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-3 inset-x-0 z-40 px-4 max-w-xl mx-auto" x-cloak>
        
        <div class="bg-gray-900 text-white rounded-2xl p-3.5 shadow-2xl flex items-center justify-between border border-gray-700/80 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="relative p-2.5 bg-brand-600 rounded-xl text-white">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    <span class="absolute -top-1.5 -right-1.5 bg-accent-500 text-gray-950 text-[10px] font-black rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center" x-text="cartTotalItems"></span>
                </div>
                <div>
                    <div class="text-[11px] text-gray-400">Total Belanja:</div>
                    <div class="text-base font-extrabold text-white" x-text="formatRupiah(cartSubtotal)"></div>
                </div>
            </div>

            <button 
                @click="openCheckoutModal = true"
                class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white font-bold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-lg transition">
                <span>Lanjut ke Pembayaran</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- CHECKOUT MODAL / DRAWER -->
    <div x-show="openCheckoutModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-end sm:items-center justify-center min-h-screen px-4 pt-4 pb-6 sm:p-0">
            <!-- Backdrop -->
            <div @click="openCheckoutModal = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Panel -->
            <div class="relative bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl z-10 max-h-[90vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-brand-50 text-brand-600">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-gray-900 text-base">Rincian Pembayaran & Checkout</h3>
                            <p class="text-xs text-gray-500">Periksa item dan pilih metode pembayaran</p>
                        </div>
                    </div>
                    <button @click="openCheckoutModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Modal Scrollable Content -->
                <div class="overflow-y-auto no-scrollbar py-4 space-y-5 flex-1">
                    
                    @guest
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-start gap-2.5 text-xs text-amber-900 shadow-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold">Wajib Masuk Sebelum Checkout:</span> Anda saat ini berbelanja sebagai tamu. Silakan <a href="{{ route('login') }}" class="font-bold underline text-brand-700 hover:text-brand-800">Masuk ke Akun</a> atau <a href="{{ route('register') }}" class="font-bold underline text-brand-700 hover:text-brand-800">Daftar</a> terlebih dahulu untuk menyelesaikan pesanan.
                            </div>
                        </div>
                    @endguest
                    
                    <!-- 1. Destination Address Summary -->
                    <div class="bg-gray-50 rounded-2xl p-3.5 border border-gray-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-4 h-4 text-brand-600"></i>
                                Alamat Pengantaran
                            </span>
                            <button @click="openCheckoutModal = false; openLocationModal = true" class="text-xs text-brand-600 font-semibold hover:underline">
                                Ubah Pin
                            </button>
                        </div>
                        <div class="text-xs text-gray-800 font-medium" x-text="deliveryAddress"></div>
                        <div class="text-[11px] text-gray-500 mt-1 flex items-center gap-2">
                            <span>Estimasi Jarak: <strong x-text="distanceKm.toFixed(1) + ' km'"></strong></span>
                            <span>•</span>
                            <span>Ongkir: <strong x-text="formatRupiah(shippingFee)"></strong></span>
                        </div>
                    </div>

                    <!-- 2. Customer Contact Inputs -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Data Penerima</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-gray-600 font-medium">Nama Lengkap</label>
                                <input type="text" x-model="customerName" class="w-full mt-1 px-3 py-2 text-xs border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-600 font-medium">No. WhatsApp / Telepon</label>
                                <input type="tel" x-model="customerPhone" class="w-full mt-1 px-3 py-2 text-xs border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-600 font-medium">Catatan Khusus untuk Toko / Kurir</label>
                            <input type="text" x-model="orderNotes" placeholder="Misal: Beras diikat kencang, titip di sekuriti jika tidak ada orang" class="w-full mt-1 px-3 py-2 text-xs border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                    </div>

                    <!-- 3. Selected Items in Cart -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Item Belanja (<span x-text="cartTotalItems"></span>)</h4>
                        <div class="space-y-2.5 max-h-48 overflow-y-auto no-scrollbar pr-1">
                            <template x-for="item in cart" :key="item.id">
                                <div class="flex items-center justify-between gap-3 p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                                    <img :src="item.image_url" class="w-12 h-12 object-cover rounded-lg flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-gray-800 truncate" x-text="item.title"></div>
                                        <div class="text-[11px] text-gray-500" x-text="`${formatRupiah(item.price)} x ${item.quantity}`"></div>
                                    </div>
                                    <!-- Controls -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <button @click="decrementCart(item.id)" class="w-6 h-6 rounded-md bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100">
                                            <i data-lucide="minus" class="w-3 h-3"></i>
                                        </button>
                                        <span class="text-xs font-bold w-5 text-center" x-text="item.quantity"></span>
                                        <button @click="incrementCart(item.id)" class="w-6 h-6 rounded-md bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100">
                                            <i data-lucide="plus" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                    <div class="text-xs font-bold text-gray-900 text-right w-20" x-text="formatRupiah(item.price * item.quantity)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 4. Payment Method Selection (WITH COD MIN ORDER RULE) -->
                    <div class="space-y-2.5">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Metode Pembayaran</h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Option QRIS -->
                            <label 
                                @click="paymentMethod = 'QRIS'"
                                :class="paymentMethod === 'QRIS' ? 'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500' : 'border-gray-200 bg-white'"
                                class="border rounded-2xl p-3 cursor-pointer transition flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-gray-900">QRIS Instan</span>
                                    <input type="radio" value="QRIS" x-model="paymentMethod" class="text-brand-600">
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1">BCA, GoPay, OVO, Dana, ShopeePay</div>
                                <span class="inline-block mt-2 text-[10px] font-bold text-brand-600">Otomatis Terverifikasi</span>
                            </label>

                            <!-- Option COD (Cash on Delivery) -->
                            <div 
                                :class="{
                                    'opacity-60 bg-gray-100 border-gray-200 cursor-not-allowed': cartSubtotal < minCodAmount,
                                    'border-brand-600 bg-brand-50/50 ring-2 ring-brand-500 cursor-pointer': paymentMethod === 'COD' && cartSubtotal >= minCodAmount,
                                    'border-gray-200 bg-white cursor-pointer': paymentMethod !== 'COD' && cartSubtotal >= minCodAmount
                                }"
                                @click="if (cartSubtotal >= minCodAmount) paymentMethod = 'COD'"
                                class="border rounded-2xl p-3 transition flex flex-col justify-between relative">
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-xs text-gray-900">COD (Bayar Tunai)</span>
                                        <template x-if="cartSubtotal < minCodAmount">
                                            <i data-lucide="lock" class="w-3.5 h-3.5 text-rose-500"></i>
                                        </template>
                                    </div>
                                    <input type="radio" value="COD" x-model="paymentMethod" :disabled="cartSubtotal < minCodAmount" class="text-brand-600">
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1">Bayar langsung ke kurir saat barang tiba</div>
                                
                                <template x-if="cartSubtotal >= minCodAmount">
                                    <span class="inline-block mt-2 text-[10px] font-bold text-emerald-600">Syarat COD Terpenuhi</span>
                                </template>
                                <template x-if="cartSubtotal < minCodAmount">
                                    <span class="inline-block mt-2 text-[10px] font-semibold text-rose-600">Min. Rp 50.000</span>
                                </template>
                            </div>
                        </div>

                        <!-- COD Warning Banner if below Rp 50.000 -->
                        <div x-show="cartSubtotal < minCodAmount" class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 flex items-start gap-2 text-xs text-amber-800" x-cloak>
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold">Opsi COD Terkunci:</span> 
                                COD hanya aktif untuk pesanan minimal <strong>Rp 50.000</strong>. Belanja Anda saat ini <strong x-text="formatRupiah(cartSubtotal)"></strong>. Tambahkan <span class="font-bold text-amber-900" x-text="formatRupiah(minCodAmount - cartSubtotal)"></span> lagi untuk mengaktifkan COD.
                            </div>
                        </div>
                    </div>

                    <!-- 5. Transparent Cost Breakdown -->
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold text-gray-900" x-text="formatRupiah(cartSubtotal)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span class="flex items-center gap-1">
                                <span>Biaya Pengiriman</span>
                                <span class="text-[10px] text-gray-400" x-text="`(${distanceKm.toFixed(1)} km)`"></span>
                            </span>
                            <span class="font-semibold text-gray-900" x-text="formatRupiah(shippingFee)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Biaya Layanan Aplikasi</span>
                            <span class="font-semibold text-gray-900" x-text="formatRupiah(serviceFee)"></span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 flex justify-between text-sm font-extrabold text-gray-900">
                            <span>Total Pembayaran</span>
                            <span class="text-brand-600 text-base" x-text="formatRupiah(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Validation Error Message Alert -->
                    <div x-show="checkoutError" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium" x-text="checkoutError" x-cloak></div>

                </div>

                <!-- Modal Footer Submit Button -->
                <div class="pt-3 border-t border-gray-100 flex-shrink-0">
                    <button 
                        @click="processCheckout()" 
                        :disabled="isSubmitting || cart.length === 0"
                        class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 active:scale-98 text-white rounded-xl font-bold text-sm shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        </template>
                        <span x-text="isSubmitting ? 'Memproses Pesanan...' : `Pesan Sekarang • ${formatRupiah(grandTotal)}`"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- PIN-POINT LOCATION MODAL (LEAFLET MAP) -->
    <div x-show="openLocationModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div @click="openLocationModal = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10 flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Atur Lokasi Pengantaran</h3>
                        <p class="text-xs text-gray-500">Geser pin peta untuk menghitung ongkir akurat</p>
                    </div>
                    <button @click="openLocationModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                <!-- Leaflet Map Container -->
                <div class="my-4 rounded-2xl overflow-hidden border border-gray-200 h-64 relative">
                    <div id="pickerMap" class="w-full h-full"></div>
                    <div class="absolute bottom-2 left-2 z-[400] bg-white/90 backdrop-blur px-2 py-1 rounded text-[11px] text-gray-700 font-semibold shadow">
                        Jarak Toko: <span x-text="distanceKm.toFixed(1)"></span> km
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Detail Alamat Lengkap</label>
                        <textarea x-model="deliveryAddress" rows="2" class="w-full mt-1 p-2.5 text-xs border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                    </div>

                    <!-- Auto-detect GPS button -->
                    <button 
                        type="button"
                        @click="detectCurrentLocation()" 
                        class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition">
                        <i data-lucide="crosshair" class="w-4 h-4 text-brand-600"></i>
                        <span>Gunakan Lokasi GPS Saya Saat Ini</span>
                    </button>
                </div>

                <div class="mt-4 pt-3 border-t">
                    <button 
                        @click="saveLocationSetting()" 
                        class="w-full py-2.5 bg-brand-600 text-white rounded-xl text-xs font-bold hover:bg-brand-700 transition">
                        Simpan Alamat & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerApp() {
    return {
        products: @json($products),
        selectedCategory: '{{ $selectedCategory }}',
        searchQuery: '{{ $search ?? "" }}',
        loading: false,

        // Customer & Delivery State
        customerName: @json($customerProfile->name ?? 'Pelanggan'),
        customerPhone: @json($customerProfile->phone ?? ''),
        deliveryAddress: @json($customerProfile->address ?? 'Jl. Erlangga Barat VII No. 12, Semarang Selatan'),
        deliveryLat: {{ (float) ($customerProfile->latitude ?? -7.0125000) }},
        deliveryLng: {{ (float) ($customerProfile->longitude ?? 110.4320000) }},
        distanceKm: {{ (float) $initialShipping['distance_km'] }},
        shippingFee: {{ (float) $initialShipping['fee'] }},
        serviceFee: 2000,
        orderNotes: '',
        isGuest: {{ Auth::check() ? 'false' : 'true' }},

        // Payment & Validation
        paymentMethod: 'QRIS',
        minCodAmount: 50000,
        checkoutError: '',
        isSubmitting: false,

        // Modals
        openCheckoutModal: false,
        openLocationModal: false,
        mapInstance: null,
        markerInstance: null,

        // Cart State (Persisted in localStorage)
        cart: [],

        init() {
            // Restore cart from localStorage if exists
            const savedCart = localStorage.getItem('sembakoku_cart');
            if (savedCart) {
                try { this.cart = JSON.parse(savedCart); } catch (e) { this.cart = []; }
            }

            this.$watch('cart', () => {
                localStorage.setItem('sembakoku_cart', JSON.stringify(this.cart));
            });

            this.$watch('openLocationModal', (val) => {
                if (val) {
                    this.$nextTick(() => this.initPickerMap());
                }
            });
        },

        get cartTotalItems() {
            return this.cart.reduce((sum, it) => sum + it.quantity, 0);
        },

        get cartSubtotal() {
            return this.cart.reduce((sum, it) => sum + (it.price * it.quantity), 0);
        },

        get grandTotal() {
            return this.cartSubtotal + this.shippingFee + this.serviceFee;
        },

        getCartItem(productId) {
            return this.cart.find(it => it.id === productId);
        },

        addToCart(product) {
            const existing = this.getCartItem(product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: product.id,
                    title: product.title,
                    price: product.price,
                    image_url: product.image_url,
                    unit: product.unit,
                    quantity: 1
                });
            }
            this.$nextTick(() => lucide.createIcons());
        },

        incrementCart(productId) {
            const item = this.getCartItem(productId);
            if (item) {
                item.quantity++;
            }
        },

        decrementCart(productId) {
            const index = this.cart.findIndex(it => it.id === productId);
            if (index !== -1) {
                if (this.cart[index].quantity > 1) {
                    this.cart[index].quantity--;
                } else {
                    this.cart.splice(index, 1);
                }
            }
            this.$nextTick(() => lucide.createIcons());
        },

        selectCategory(cat) {
            this.selectedCategory = cat;
            this.performLiveSearch();
        },

        performLiveSearch() {
            this.loading = true;
            fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}&category=${encodeURIComponent(this.selectedCategory)}`)
                .then(res => res.json())
                .then(data => {
                    this.products = data.products;
                    this.loading = false;
                    this.$nextTick(() => lucide.createIcons());
                })
                .catch(err => {
                    this.loading = false;
                    console.error('Search error', err);
                });
        },

        formatRupiah(amount) {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        },

        initPickerMap() {
            const storeLat = {{ (float) $storeSettings->store_latitude }};
            const storeLng = {{ (float) $storeSettings->store_longitude }};

            if (!this.mapInstance) {
                this.mapInstance = L.map('pickerMap').setView([this.deliveryLat, this.deliveryLng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.mapInstance);

                // Store Marker
                L.marker([storeLat, storeLng], {
                    icon: L.divIcon({
                        className: 'custom-store-pin',
                        html: '<div class="bg-brand-600 text-white p-1.5 rounded-full shadow-lg border-2 border-white"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg></div>'
                    })
                }).addTo(this.mapInstance).bindPopup('<strong>' + @json($storeSettings->store_name) + '</strong><br>' + @json($storeSettings->store_address));

                // Destination draggable marker
                this.markerInstance = L.marker([this.deliveryLat, this.deliveryLng], {
                    draggable: true,
                    icon: L.divIcon({
                        className: 'custom-dest-pin',
                        html: '<div class="bg-rose-500 text-white p-1.5 rounded-full shadow-lg border-2 border-white"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg></div>'
                    })
                }).addTo(this.mapInstance);

                this.markerInstance.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.updateCoords(pos.lat, pos.lng);
                });

                this.mapInstance.on('click', (e) => {
                    this.markerInstance.setLatLng(e.latlng);
                    this.updateCoords(e.latlng.lat, e.latlng.lng);
                });
            } else {
                this.mapInstance.invalidateSize();
            }
        },

        detectCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        if (this.markerInstance) {
                            this.markerInstance.setLatLng([lat, lng]);
                            this.mapInstance.setView([lat, lng], 15);
                        }
                        this.updateCoords(lat, lng);
                    },
                    (err) => alert('Gagal membaca GPS browser. Silakan klik langsung di peta.')
                );
            }
        },

        updateCoords(lat, lng) {
            this.deliveryLat = lat;
            this.deliveryLng = lng;

            // Recalculate shipping
            fetch('/api/shipping-fee', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ lat: lat, lng: lng })
            })
            .then(res => res.json())
            .then(data => {
                this.distanceKm = data.distance_km;
                this.shippingFee = data.shipping_fee;
            });
        },

        saveLocationSetting() {
            this.openLocationModal = false;
        },

        processCheckout() {
            this.checkoutError = '';

            // Guard: Must login before checkout
            if (this.isGuest) {
                alert('Silakan masuk ke akun pelanggan Anda terlebih dahulu untuk menyelesaikan pesanan.');
                window.location.href = "{{ route('login') }}";
                return;
            }
            
            // Client-side guard for COD
            if (this.paymentMethod === 'COD' && this.cartSubtotal < this.minCodAmount) {
                this.checkoutError = `Opsi COD hanya berlaku untuk pesanan minimal ${this.formatRupiah(this.minCodAmount)}. Silakan gunakan QRIS atau tambah belanjaan Anda.`;
                return;
            }

            this.isSubmitting = true;

            const payload = {
                customer_name: this.customerName,
                customer_phone: this.customerPhone,
                delivery_address: this.deliveryAddress,
                delivery_lat: this.deliveryLat,
                delivery_lng: this.deliveryLng,
                payment_method: this.paymentMethod,
                notes: this.orderNotes,
                cart_items: this.cart.map(it => ({ id: it.id, quantity: it.quantity }))
            };

            fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(async (res) => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal membuat pesanan'));
                }
                return data;
            })
            .then(data => {
                // Clear cart on success
                this.cart = [];
                localStorage.removeItem('sembakoku_cart');
                // Redirect to Live Tracking page
                window.location.href = data.redirect_url;
            })
            .catch(err => {
                this.isSubmitting = false;
                this.checkoutError = err.message;
            });
        }
    };
}
</script>
@endpush

