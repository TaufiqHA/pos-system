<!-- ================= SIDEBAR CABANG ================= -->
<aside
    class="sidebar w-64 flex flex-col justify-between h-full border-r border-gray-800 flex-shrink-0 fixed lg:relative inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <!-- Logo & Nama Perusahaan / Cabang -->
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="w-8 h-8 bg-gradient-to-tr from-[#B4F481] to-green-400 rounded-lg flex items-center justify-center shadow-lg shadow-green-500/20">
                    <span class="text-black font-black text-sm tracking-tighter">L</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-green-400 tracking-wider font-display">LUCIFER</h1>
                    <p class="text-[9px] text-gray-400 tracking-widest uppercase font-semibold">Kantor Cabang</p>
                </div>
            </div>
            <!-- Tombol Close (Hanya muncul di Mobile/Tablet) -->
            <button @click="sidebarOpen = false"
                class="lg:hidden text-gray-400 hover:text-white focus:outline-none p-1 rounded-lg hover:bg-gray-800 transition cursor-pointer"
                aria-label="Tutup Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigasi Menu -->
    <nav class="flex-1 px-4 space-y-4 overflow-y-auto pb-4">
        <!-- Dashboard -->
        <a href=" {{ route('cabang.dashboard') }} "
            class="block {{ request()->routeIs('cabang.dashboard') ? 'bg-indigo-950/60 text-white border border-indigo-700/50 shadow-md shadow-indigo-950/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }} rounded-xl p-3 flex items-center space-x-3 transition cursor-pointer">
            <svg class="w-5 h-5 {{ request()->routeIs('cabang.dashboard') ? 'text-[#B4F481]' : 'text-gray-400' }}"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                </path>
            </svg>
            <span class="font-medium text-sm">Dashboard</span>
        </a>

        <!-- PRODUK & STOK (Dropdown) -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition"
                onclick="toggleDropdown('produk-menu', 'produk-icon')">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="text-[10px] font-bold tracking-wider uppercase font-display">Produk & Stok</span>
                </div>
                <svg id="produk-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="produk-menu"
                class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li
                    class="hover:text-white transition {{ request()->routeIs('cabang.monitoring-stok') ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('cabang.monitoring-stok') }}" class="block w-full">Monitoring Stok</a>
                </li>
                <li
                    class="hover:text-white transition {{ request()->routeIs('stock-histories.index') ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('stock-histories.index') }}" class="block w-full">Riwayat Stok</a>
                </li>
                <li
                    class="hover:text-white transition {{ request()->routeIs('product-branch-prices.index') ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('product-branch-prices.index') }}" class="block w-full">Atur Harga Cabang</a>
                </li>
            </ul>
        </div>

        <!-- TRANSAKSI (Dropdown) -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition"
                onclick="toggleDropdown('transaksi-menu', 'transaksi-icon')">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span class="text-[10px] font-bold tracking-wider uppercase font-display">Transaksi</span>
                </div>
                <svg id="transaksi-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="transaksi-menu"
                class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="{{ request()->routeIs('cabang.penjualan') ? 'text-[#B4F481] font-bold' : '' }} hover:text-white cursor-pointer transition">
                     <a href="{{ route('cabang.penjualan') }}" class="block w-full">Penjualan</a>
                </li>
                <li
                    class="hover:text-white cursor-pointer transition {{ request()->routeIs('purchase-orders.index') && request('action') !== 'create' ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('purchase-orders.index') }}" class="block w-full">Daftar PO</a>
                </li>
                <li class="hover:text-white cursor-pointer transition {{ request()->routeIs('cabang.pengiriman') ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('cabang.pengiriman') }}" class="block w-full">Pengiriman</a>
                </li>
            </ul>
        </div>

        <!-- DATA CABANG (Dropdown) -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition"
                onclick="toggleDropdown('cabang-menu', 'cabang-icon')">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span class="text-[10px] font-bold tracking-wider uppercase font-display">Data Cabang</span>
                </div>
                <svg id="cabang-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="cabang-menu"
                class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="{{ request()->routeIs('outlets.index') ? 'text-[#B4F481] font-bold' : '' }} hover:text-white cursor-pointer transition">
    <a href="{{ route('outlets.index') }}" class="block w-full">Outlet</a>
</li>
            </ul>
        </div>

        <!-- KEUANGAN (Dropdown) -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition"
                onclick="toggleDropdown('keuangan-menu', 'keuangan-icon')">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span class="text-[10px] font-bold tracking-wider uppercase font-display">Keuangan</span>
                </div>
                <svg id="keuangan-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="keuangan-menu"
                class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white cursor-pointer transition {{ request()->routeIs('cabang.laporan') ? 'text-[#B4F481] font-bold' : '' }}">
                    <a href="{{ route('cabang.laporan') }}" class="block w-full">Laporan Keuangan</a>
                </li>
                <li class="{{ request()->routeIs('cabang.hutang') ? 'text-[#B4F481] font-bold' : '' }} hover:text-white cursor-pointer transition">
                    <a href="{{ route('cabang.hutang') }}" class="block w-full">Hutang</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Profil & Logout -->
    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center space-x-3 mb-4 cursor-pointer hover:bg-gray-800/50 p-2 rounded-xl transition">
            <div
                class="w-8 h-8 rounded-full bg-indigo-900 text-indigo-300 border border-indigo-700 flex items-center justify-center font-bold text-xs select-none">
                {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-sm text-white line-clamp-1">{{ Auth::user()->name ?? 'User Cabang' }}</p>
                <p class="text-[9px] text-gray-400 font-semibold tracking-wider uppercase">CABANG</p>
            </div>
        </div>

        <!-- CSRF Protected Logout Form -->
        <form id="logout-form" action="{{ url('/auth/logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="text-red-500 hover:text-red-400 flex items-center space-x-2 text-sm font-semibold w-full p-2 rounded-lg hover:bg-red-500/10 transition cursor-pointer">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                </path>
            </svg>
            <span>Keluar</span>
        </button>
    </div>
</aside>

<!-- Overlay / Backdrop saat sidebar terbuka di layar kecil -->
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm" style="display: none;"></div>