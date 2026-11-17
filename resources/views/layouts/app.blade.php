<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toko Sembako Modern') - Delivery Sembako Cepat & Segar</title>

    <!-- Tailwind CSS (Play CDN for instant reliable rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669', // Primary Emerald
                            700: '#047857',
                            800: '#065f46',
                        },
                        accent: {
                            400: '#fbbf24',
                            500: '#f59e0b', // Warm Amber
                            600: '#d97706',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Leaflet Maps CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-subtle {
            animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans text-gray-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- TOP AUTH & ROLE NAVIGATION BAR -->
    <header class="bg-gray-900 text-white text-xs border-b border-gray-800 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-2 flex flex-wrap items-center justify-between gap-3">
            
            <!-- Brand / Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2 group">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-brand-600 text-white font-bold text-xs shadow-md group-hover:bg-brand-500 transition">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </span>
                <span class="font-bold tracking-tight text-white sm:text-sm">Toko Sembako Modern</span>
                <span class="bg-brand-500/20 text-brand-400 border border-brand-500/30 px-1.5 py-0.5 rounded text-[10px] font-medium hidden md:inline">
                    Semarang Selatan
                </span>
            </a>

            <!-- Right Navigation: Dynamic based on Auth Status -->
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                
                @guest
                    <!-- Guest Navigation (Belum Login) -->
                    <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-xl font-medium text-gray-300 hover:text-white transition flex items-center gap-1.5">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>Katalog</span>
                    </a>
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-gray-800 hover:bg-gray-700 text-gray-200 transition flex items-center gap-1.5 border border-gray-700">
                        <i data-lucide="log-in" class="w-3.5 h-3.5 text-brand-400"></i>
                        <span>Masuk</span>
                    </a>
                    <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-xl font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        <span>Daftar</span>
                    </a>
                @endguest

                @auth
                    <!-- Role-Specific Shortcuts -->
                    <nav class="flex items-center gap-1 sm:gap-1.5">
                        <a href="{{ route('home') }}" class="px-2.5 py-1 rounded-lg transition font-medium flex items-center gap-1 {{ request()->routeIs('home') ? 'bg-brand-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Katalog Belanja</span>
                        </a>

                        @if(Auth::user()->isAdmin())
                            <!-- Admin can access Admin Dashboard AND Courier Driver View -->
                            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1 rounded-lg transition font-medium flex items-center gap-1 {{ request()->routeIs('admin.*') ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-rose-400"></i>
                                <span>Admin Kasir</span>
                            </a>
                            <a href="{{ route('driver.dashboard') }}" class="px-2.5 py-1 rounded-lg transition font-medium flex items-center gap-1 {{ request()->routeIs('courier.*') || request()->routeIs('driver.*') ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}" title="Akses Tampilan Kurir">
                                <i data-lucide="bike" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span class="hidden md:inline">Tampilan Driver</span>
                            </a>
                        @elseif(Auth::user()->isDriver())
                            <!-- Driver access -->
                            <a href="{{ route('driver.dashboard') }}" class="px-2.5 py-1 rounded-lg transition font-medium flex items-center gap-1 {{ request()->routeIs('courier.*') || request()->routeIs('driver.*') ? 'bg-brand-600 text-white shadow-sm' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                                <i data-lucide="bike" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Dashboard Kurir</span>
                            </a>
                        @endif
                    </nav>

                    <!-- Authenticated User Profile & Logout -->
                    <div class="flex items-center gap-2 pl-2 border-l border-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gray-800 border border-gray-700 flex items-center justify-center font-bold text-xs text-brand-400">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="font-bold text-white text-[11px] leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                                <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider {{ Auth::user()->role_badge_class }}">
                                    {{ Auth::user()->role_label }}
                                </span>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-400 hover:bg-gray-800 rounded-lg transition" title="Keluar dari Akun">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endauth

            </div>
        </div>
    </header>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-emerald-600 text-white text-sm px-4 py-3 shadow-md flex items-center justify-between transition" x-cloak>
            <div class="max-w-7xl mx-auto flex items-center gap-2 w-full">
                <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-white hover:text-emerald-100"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" class="bg-rose-600 text-white text-sm px-4 py-3 shadow-md flex items-center justify-between transition" x-cloak>
            <div class="max-w-7xl mx-auto flex items-center gap-2 w-full">
                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-white hover:text-rose-100"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    @endif

    <!-- MAIN CONTENT -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-gray-200 mt-auto py-6 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700">Toko Sembako Modern</span>
                <span>•</span>
                <span>GoMart / GrabMart Style Delivery Engine</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Reverb Real-Time Active
                </span>
                <span>•</span>
                <span>PHP 8.4 + Laravel 13</span>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        window.addEventListener('alpine:initialized', () => {
            lucide.createIcons();
        });

        // Simple Audio Synthesizer for alerts without needing external audio files
        window.playAlertSound = function(type = 'chime') {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                
                if (type === 'order') {
                    // Double pleasant chime
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now); // D5
                    osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5
                    gain1.gain.setValueAtTime(0.3, now);
                    gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.4);

                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.2);
                    osc2.frequency.exponentialRampToValueAtTime(1174.66, now + 0.35); // D6
                    gain2.gain.setValueAtTime(0.35, now + 0.2);
                    gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.65);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.2);
                    osc2.stop(now + 0.65);
                }
            } catch (e) {
                console.warn('AudioContext not allowed without prior user interaction');
            }
        };
    </script>
    @stack('scripts')
</body>
</html>

