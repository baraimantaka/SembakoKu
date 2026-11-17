@extends('layouts.app')

@section('title', 'Masuk Akun - Toko Sembako Modern')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-6">

        <!-- LOGO & BRAND HEADER -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-brand-600 text-white shadow-xl shadow-brand-600/30 mb-3">
                <i data-lucide="store" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Toko Sembako Modern
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Masuk ke akun Anda untuk belanja sembako instan & tracking kurir
            </p>
        </div>

        <!-- MAIN LOGIN CARD -->
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

            <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                @csrf

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
                            id="loginEmail"
                            value="{{ old('email') }}" 
                            required 
                            placeholder="nama@email.com" 
                            class="w-full pl-10 pr-3.5 py-3 text-xs sm:text-sm border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="loginPassword"
                            required 
                            placeholder="••••••••" 
                            class="w-full pl-10 pr-3.5 py-3 text-xs sm:text-sm border border-gray-200 rounded-2xl outline-none focus:ring-2 focus:ring-brand-500 transition">
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                        <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 active:scale-98 text-white rounded-2xl text-xs sm:text-sm font-bold shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-5 text-center text-xs text-gray-500">
                Belum memiliki akun pelanggan? 
                <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700 underline">
                    Daftar Sekarang
                </a>
            </div>

            <!-- DIVIDER -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-3 text-gray-400 font-semibold tracking-wider">
                        ⚡ Quick Demo Login (1-Klik)
                    </span>
                </div>
            </div>

            <!-- ONE-CLICK QUICK DEMO LOGIN BUTTONS -->
            <div class="space-y-2.5">
                <p class="text-[11px] text-gray-500 text-center mb-2">
                    Pilih akun siap pakai untuk menguji fitur multi-role tanpa mengetik password:
                </p>

                <!-- 1. Customer Demo -->
                <form action="{{ route('login.quick') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="customer">
                    <button type="submit" class="w-full p-3 rounded-2xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-100/80 active:scale-98 transition flex items-center justify-between text-left group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900">Pelanggan (Customer)</div>
                                <div class="text-[10px] text-gray-500">pelanggan@sembako.test</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-brand-700 group-hover:translate-x-0.5 transition flex items-center gap-1">
                            <span>Masuk</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </button>
                </form>

                <!-- 2. Admin Kasir Demo -->
                <form action="{{ route('login.quick') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="admin">
                    <button type="submit" class="w-full p-3 rounded-2xl border border-rose-200 bg-rose-50/60 hover:bg-rose-100/80 active:scale-98 transition flex items-center justify-between text-left group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900">Admin Toko / Kasir</div>
                                <div class="text-[10px] text-gray-500">admin@sembako.test</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-rose-700 group-hover:translate-x-0.5 transition flex items-center gap-1">
                            <span>Masuk</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </button>
                </form>

                <!-- 3. Driver Kurir Demo -->
                <form action="{{ route('login.quick') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="driver">
                    <button type="submit" class="w-full p-3 rounded-2xl border border-amber-200 bg-amber-50/60 hover:bg-amber-100/80 active:scale-98 transition flex items-center justify-between text-left group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500 text-gray-950 flex items-center justify-center">
                                <i data-lucide="bike" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900">Kurir Driver Toko</div>
                                <div class="text-[10px] text-gray-500">driver@sembako.test</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-amber-800 group-hover:translate-x-0.5 transition flex items-center gap-1">
                            <span>Masuk</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
