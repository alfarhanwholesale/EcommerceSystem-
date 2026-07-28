<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'AlfarhanWholesale') }} - Makanan Sunnah & Wangian</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .serif-font {
            font-family: 'Playfair Display', serif;
        }

        /* Mobile menu */
        #mobile-menu { display: none; }
        #mobile-menu.open { display: block; }
        .site-search {
            position: relative;
        }
        .site-search-input {
            width: 100%;
            padding-left: 1rem;
            padding-right: 2.75rem;
        }
        .site-search-icon,
        .site-search-button {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #059669;
            line-height: 1;
        }
        .site-search-icon {
            pointer-events: none;
        }

        /* Smooth transitions */
        * { -webkit-tap-highlight-color: transparent; }

        /* Better touch targets */
        @media (max-width: 768px) {
            nav a, header a { min-height: 44px; display: flex; align-items: center; }
        }

        @media (min-width: 768px) {
            #mobile-menu,
            #mobile-menu.open {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <!-- Top Promo bar -->
    <div class="bg-emerald-950 text-emerald-100 text-xs py-2 px-4 text-center font-medium tracking-wide">
         Daftar sekarang dan dapatkan <strong>diskaun 20%</strong> untuk pembelian pertama anda! ✨
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-emerald-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <!-- Logo with image -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('shop.home') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo.png') }}" alt="AlfarhanWholesale Logo" class="h-12 w-12 md:h-14 md:w-14 object-contain rounded-full">
                        <div class="hidden sm:flex flex-col leading-tight">
                            <span class="text-base md:text-xl font-extrabold text-emerald-800 tracking-tight">Alfarhan<span class="text-emerald-500 font-light font-serif">Wholesale</span></span>
                            <span class="text-[10px] text-slate-400 font-medium tracking-wider uppercase">Trade & Wholesale</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('shop.home') }}" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Home</a>
                    <a href="{{ route('shop.index') }}" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">Shop Catalog</a>
                    <a href="{{ route('shop.about') }}" class="text-slate-600 hover:text-emerald-700 font-medium transition-colors">About Us</a>
                </nav>

                <!-- Search, Cart & Profile actions -->
                <div class="flex items-center gap-2 md:gap-4">
                    <!-- Search Bar (Desktop only) -->
                    <form action="{{ route('shop.index') }}" method="GET" class="hidden lg:block site-search w-52 xl:w-60">
                        <input type="text" name="search" placeholder="Cari produk sunnah..."
                            value="{{ request('search') }}"
                            class="site-search-input py-2 border border-emerald-100 rounded-full bg-emerald-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition-all">
                        <div class="site-search-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </form>

                    <!-- Cart -->
                    <a href="{{ route('shop.cart') }}" class="p-2 text-slate-600 hover:text-emerald-700 relative transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        @auth
                            @php
                                $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute top-0 right-0 bg-emerald-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center border border-white">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        @endauth
                    </a>

                    <!-- User authentication (Desktop) -->
                    @auth
                        <div class="relative group hidden md:block">
                            <button class="flex items-center gap-1 text-slate-700 hover:text-emerald-700 font-medium transition-colors py-2 focus:outline-none">
                                <span class="bg-emerald-100 text-emerald-800 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden lg:inline text-sm">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <!-- Dropdown -->
                            <div class="absolute right-0 top-full mt-1 w-48 bg-white border border-slate-100 rounded-lg shadow-lg py-2 hidden group-hover:block hover:block z-50">
                                @if(in_array(auth()->user()->role, ['admin', 'outdoor_sales', 'purchaser', 'storekeeper']))
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">Admin Dashboard</a>
                                @endif
                                <a href="{{ route('customer.orders') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">My Orders</a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign Out</a>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:flex items-center gap-2">
                            <a href="{{ route('login') }}" class="text-emerald-700 hover:text-emerald-900 font-semibold text-sm transition-colors">Login</a>
                            <a href="{{ route('register') }}" class="bg-emerald-700 text-white hover:bg-emerald-800 font-medium px-4 py-2 rounded-full text-sm transition-all shadow-xs hover:shadow-md">Sign Up</a>
                        </div>
                    @endauth

                    <!-- Hamburger (Mobile) -->
                    <button id="hamburger-btn" class="md:hidden p-2 text-slate-600 hover:text-emerald-700 focus:outline-none" aria-label="Open Menu">
                        <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden bg-white border-t border-emerald-100 shadow-lg">
            <!-- Mobile Search -->
            <form action="{{ route('shop.index') }}" method="GET" class="site-search">
                <input type="text" name="search" placeholder="Cari produk sunnah..."
                        value="{{ request('search') }}"
                        class="site-search-input py-3 border border-emerald-100 rounded-full bg-emerald-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition-all">
                <div class="site-search-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>
            <!-- Mobile Nav Links -->
            <nav class="px-4 pb-4 space-y-1">
                <a href="{{ route('shop.home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-medium transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Home
                </a>
                <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-medium transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    Shop Catalog
                </a>
                <a href="{{ route('shop.about') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-medium transition-all">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    About Us
                </a>

                @auth
                    <div class="border-t border-emerald-100 my-2"></div>
                    <div class="flex items-center gap-3 px-4 py-3">
                        <span class="bg-emerald-100 text-emerald-800 w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if(in_array(auth()->user()->role, ['admin', 'outdoor_sales', 'purchaser', 'storekeeper']))
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-emerald-50 font-medium transition-all text-sm">
                            🖥️ Admin Dashboard
                        </a>
                    @endif
                    <a href="{{ route('customer.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-emerald-50 font-medium transition-all text-sm">
                        📦 My Orders
                    </a>
                    <a href="{{ route('logout') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 font-medium transition-all text-sm">
                        🚪 Sign Out
                    </a>
                @else
                    <div class="border-t border-emerald-100 my-2"></div>
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-emerald-600 text-emerald-700 font-bold transition-all text-sm">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-700 text-white font-bold transition-all text-sm hover:bg-emerald-800">
                        Daftar
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Success & Error Toast Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 p-4 rounded-r-lg shadow-xs flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-rose-50 border-l-4 border-rose-600 text-rose-800 p-4 rounded-r-lg shadow-xs flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-emerald-950 text-emerald-100/80 border-t border-emerald-900 mt-16 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            <div class="space-y-4 sm:col-span-2 md:col-span-1">
                <!-- Footer Logo -->
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="AlfarhanWholesale Logo" class="w-12 h-12 rounded-full object-contain bg-white/10 p-1">
                    <div>
                        <span class="block text-xl font-bold text-white tracking-tight">
                            Alfarhan<span class="text-emerald-400 font-serif">Wholesale</span>
                        </span>
                        <span class="text-[10px] text-emerald-400/80 uppercase tracking-widest">Trade & Wholesale</span>
                    </div>
                </div>
                <p class="text-sm text-emerald-200/60 leading-relaxed">
                    Pemborong produk sunnah pilihan — kurma, madu, wangian, bakhoor, dan lebih lagi — dengan harga borong yang berpatutan.
                </p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm tracking-wider uppercase">Shop Categories</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.index', ['category' => 'dates']) }}" class="hover:text-white transition-colors">Dates</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'honey']) }}" class="hover:text-white transition-colors">Honey</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'perfume']) }}" class="hover:text-white transition-colors">Perfume</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'bakhoor']) }}" class="hover:text-white transition-colors">Bakhoor</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'others']) }}" class="hover:text-white transition-colors">Others</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm tracking-wider uppercase">Useful Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white transition-colors">All Products</a></li>
                    <li><a href="{{ route('shop.cart') }}" class="hover:text-white transition-colors">Shopping Cart</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">My Profile</a></li>
                    <li><a href="{{ route('admin.login') }}" class="hover:text-white transition-colors">Staff Login</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h4 class="text-white font-semibold text-sm tracking-wider uppercase">Contact Us</h4>
                <p class="text-sm leading-relaxed">
                    48, Jalan Permai 2, Taman Puchong Permai,<br>
                    47150 Puchong, Selangor, Malaysia<br><br>
                    alfarhanwholesale@gmail.com<br>
                    +012-963 2548
                </p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-emerald-900/60 mt-8 pt-6 text-center text-xs text-emerald-200/40">
            &copy; {{ date('Y') }} AlfarhanWholesale. All rights reserved. Made with love and devotion.
        </div>
    </footer>

    <script>
        // Hamburger menu toggle
        const btn = document.getElementById('hamburger-btn');
        const menu = document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        if (btn) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('open');
                hamburgerIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });
        }
    </script>

    @yield('scripts')
    @stack('scripts')

</body>
</html>