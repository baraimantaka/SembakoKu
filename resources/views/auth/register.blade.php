@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan - Toko Sembako Modern')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-6">

        <!-- LOGO & BRAND HEADER -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-brand-600 text-white shadow-xl shadow-brand-600/30 mb-3">
                <i data-lucide="user-plus" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Daftar Akun Pelanggan
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Nikmati kemudahan pesan sembako kilat 15-30 menit sampai di depan pintu
            </p>
        </div>

        <!-- MAIN REGISTER CARD -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-gray-100">
            
            @if($errors->any())
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-2.5 text-xs text-rose-700">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5 text-rose-600"></i>
                    <div>
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('register.process') }}" method="POST" class="space-y-4 text-xs sm:text-sm">
                @csrf

                <!-- Name Input -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required 
                            placeholder="Misal: Siti Nurhaliza" 
                            class="w-full pl-10 pr-3.5 py-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                </div>

                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                        <input 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            placeholder="nama@email.com" 
                            class="w-full pl-10 pr-3.5 py-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                </div>

                <!-- Phone Input -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nomor WhatsApp / HP
                    </label>
                    <div class="relative">
                        <i data-lucide="phone" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                        <input 
                            type="tel" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            required 
                            placeholder="081234567890" 
                            class="w-full pl-10 pr-3.5 py-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Digunakan kurir untuk konfirmasi kedatangan barang.</span>
                </div>

                <!-- Password Input -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                            <input 
                                type="password" 
                                name="password" 
                                required 
                                placeholder="Min. 6 digit" 
                                class="w-full pl-10 pr-3.5 py-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Ulangi Sandi
                        </label>
                        <div class="relative">
                            <i data-lucide="check-check" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                required 
                                placeholder="Ulangi sandi" 
                                class="w-full pl-10 pr-3.5 py-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Address (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Alamat Rumah / Pengiriman (Opsional)
                    </label>
                    <textarea 
                        name="address" 
                        rows="2" 
                        placeholder="Misal: Jl. Erlangga Barat VII No. 12, Pleburan, Semarang Selatan" 
                        class="w-full p-3 border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition text-xs">{{ old('address') }}</textarea>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 active:scale-98 text-white rounded-2xl text-xs sm:text-sm font-bold shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Daftar Akun Pelanggan</span>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-5 text-center text-xs text-gray-500">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 underline">
                    Masuk ke Akun
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
