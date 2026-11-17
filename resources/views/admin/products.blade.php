@extends('layouts.app')

@section('title', 'Manajemen Produk Sembako - Admin Toko Sembako')

@section('content')
<div x-data="adminProductsApp()" class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER & ACTIONS -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.orders') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-extrabold text-gray-900 tracking-tight">Manajemen Katalog Produk Sembako</h1>
                        <p class="text-xs text-gray-500">Kelola stok, harga, satuan, dan quick toggle status barang</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.orders') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                    <span>Pesanan Masuk</span>
                </a>
                <a href="{{ route('admin.products') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white shadow-sm flex items-center gap-1.5 flex-shrink-0">
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
                <button 
                    @click="openAddModal = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md flex-shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>

        <!-- CATEGORY TABS -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <a href="{{ route('admin.products', ['category' => 'ALL']) }}" 
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 {{ $category === 'ALL' ? 'bg-brand-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('admin.products', ['category' => $cat]) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 {{ $category === $cat ? 'bg-brand-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-200">
                        <tr>
                            <th class="py-3.5 px-4">Produk</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga / Satuan</th>
                            <th class="py-3.5 px-4">Stok Fisik</th>
                            <th class="py-3.5 px-4">Rekap Terjual</th>
                            <th class="py-3.5 px-4 text-center">Quick Toggle Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi Edit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50/80 transition" id="row-{{ $product->id }}">
                                <!-- Image & Title -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="w-12 h-12 object-cover rounded-xl border flex-shrink-0">
                                        <div>
                                            <div class="font-bold text-gray-900 line-clamp-1">{{ $product->title }}</div>
                                            <div class="text-[11px] text-gray-400 mt-0.5">ID: {{ substr($product->id, 0, 8) }}...</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-[11px] font-semibold">
                                        {{ $product->category }}
                                    </span>
                                </td>

                                <!-- Price / Unit -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-extrabold text-gray-900">{{ $product->formatted_price }}</div>
                                    <div class="text-[11px] text-gray-400">per {{ $product->unit }}</div>
                                </td>

                                <!-- Stock Status -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold {{ $product->stock <= 0 ? 'text-rose-600' : ($product->stock < 5 ? 'text-amber-600' : 'text-gray-900') }}">
                                        {{ $product->stock }} {{ $product->unit }}
                                    </div>
                                    <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.2 rounded-full border {{ $product->stock_badge['class'] }}">
                                        {{ $product->stock_badge['text'] }}
                                    </span>
                                </td>

                                <!-- Total Sold Counter -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-bold text-gray-800 bg-amber-50 text-amber-800 px-2 py-0.5 rounded-md text-[11px] border border-amber-200">
                                        <i data-lucide="flame" class="w-3 h-3 text-amber-500"></i>
                                        <span>{{ $product->formatted_sold }}</span>
                                    </span>
                                </td>

                                <!-- Quick Toggle Action (Instant AJAX without reload) -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button 
                                        type="button"
                                        @click="toggleProduct('{{ $product->id }}')" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="statusMap['{{ $product->id }}'] ?? {{ $product->is_active ? 'true' : 'false' }} ? 'bg-brand-600' : 'bg-gray-200'">
                                        <span class="sr-only">Toggle Ketersediaan</span>
                                        <span 
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="statusMap['{{ $product->id }}'] ?? {{ $product->is_active ? 'true' : 'false' }} ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                    <div class="text-[10px] text-gray-400 mt-0.5" x-text="(statusMap['{{ $product->id }}'] ?? {{ $product->is_active ? 'true' : 'false' }}) ? 'Ready' : 'Nonaktif'"></div>
                                </td>

                                <!-- Edit Modal Trigger -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button 
                                        @click="openEditModal({{ json_encode($product) }})"
                                        class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition" title="Edit Produk">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">Belum ada produk di kategori ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>

    </div>

    <!-- MODAL ADD PRODUCT -->
    <div x-show="openAddModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div @click="openAddModal = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10">
                <div class="flex items-center justify-between pb-3 border-b">
                    <h3 class="font-bold text-gray-900 text-base">Tambah Produk Sembako Baru</h3>
                    <button @click="openAddModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="font-semibold text-gray-700">Nama Produk Sembako</label>
                        <input type="text" name="title" required placeholder="Misal: Beras Setra Ramos 5kg" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-semibold text-gray-700">Kategori</label>
                            <select name="category" required class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Satuan</label>
                            <select name="unit" required class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                                <option value="kg">kg</option>
                                <option value="liter">liter</option>
                                <option value="pcs">pcs</option>
                                <option value="galon">galon</option>
                                <option value="dus">dus</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-semibold text-gray-700">Harga Jual (Rp)</label>
                            <input type="number" name="price" required min="0" placeholder="75000" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Stok Awal</label>
                            <input type="number" name="stock" required min="0" placeholder="50" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="font-semibold text-gray-700">URL Foto Produk</label>
                        <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="pt-3 border-t">
                        <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-md transition">
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PRODUCT -->
    <div x-show="openEditModalState" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div @click="openEditModalState = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl z-10">
                <div class="flex items-center justify-between pb-3 border-b">
                    <h3 class="font-bold text-gray-900 text-base">Edit Produk Sembako</h3>
                    <button @click="openEditModalState = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                <form :action="`/admin/products/${editItem.id}`" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="font-semibold text-gray-700">Nama Produk</label>
                        <input type="text" name="title" x-model="editItem.title" required class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-semibold text-gray-700">Kategori</label>
                            <select name="category" x-model="editItem.category" required class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Satuan</label>
                            <input type="text" name="unit" x-model="editItem.unit" required class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="font-semibold text-gray-700">Harga (Rp)</label>
                            <input type="number" name="price" x-model="editItem.price" required min="0" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Stok</label>
                            <input type="number" name="stock" x-model="editItem.stock" required min="0" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Total Terjual</label>
                            <input type="number" name="total_sold" x-model="editItem.total_sold" required min="0" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="font-semibold text-gray-700">URL Foto</label>
                        <input type="url" name="image_url" x-model="editItem.image_url" class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>

                    <div class="pt-3 border-t">
                        <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-md transition">
                            Perbarui Produk
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
function adminProductsApp() {
    return {
        openAddModal: false,
        openEditModalState: false,
        editItem: {},
        statusMap: {},

        toggleProduct(productId) {
            fetch(`/admin/products/${productId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                this.statusMap[productId] = data.is_active;
            })
            .catch(err => console.error('Toggle error', err));
        },

        openEditModal(product) {
            this.editItem = { ...product };
            this.openEditModalState = true;
            this.$nextTick(() => lucide.createIcons());
        }
    };
}
</script>
@endpush

