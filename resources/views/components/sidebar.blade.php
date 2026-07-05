<!-- ================= SIDEBAR KIRI ================= -->
<aside 
    class="sidebar w-64 flex flex-col justify-between h-full border-r border-gray-800 flex-shrink-0 fixed lg:relative inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <!-- Logo & Nama Perusahaan -->
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-tr from-[#B4F481] to-green-400 rounded-lg flex items-center justify-center shadow-lg shadow-green-500/20">
                    <span class="text-black font-black text-sm tracking-tighter">L</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-green-400 tracking-wider font-display">LUCIFER</h1>
                    <p class="text-[9px] text-gray-400 tracking-widest uppercase font-semibold">Kantor Pusat</p>
                </div>
            </div>
            <!-- Tombol Close (Hanya muncul di Mobile/Tablet) -->
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white focus:outline-none p-1 rounded-lg hover:bg-gray-800 transition cursor-pointer" aria-label="Tutup Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigasi Menu -->
    <nav class="flex-1 px-4 space-y-4 overflow-y-auto pb-4">
        <!-- Menu Aktif (Dashboard) -->
        <a href="{{ route('admin.dashboard') }}" class="block {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-950/60 text-white border border-indigo-700/50 shadow-md shadow-indigo-950/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }} rounded-xl p-3 flex items-center space-x-3 transition cursor-pointer">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-[#B4F481]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span class="font-medium text-sm">Dashboard</span>
        </a>

        <!-- Menu Kategori: Produk & Stok -->
        <div class="space-y-2">
            <div class="flex justify-between items-center {{ request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('suppliers.*') || request()->routeIs('admin.monitoring-stock') ? 'text-white' : 'text-gray-400' }} px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('produk-menu', 'produk-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Produk & Stok</span>
                <svg id="produk-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="produk-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white transition {{ request()->routeIs('products.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('products.index') }}" class="block w-full">Daftar Produk</a>
                </li>
                <li class="hover:text-white transition {{ request()->routeIs('categories.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('categories.index') }}" class="block w-full">Daftar Kategori</a>
                </li>
                <li class="hover:text-white transition {{ request()->routeIs('admin.monitoring-stock') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('admin.monitoring-stock') }}" class="block w-full">Monitoring Stok</a>
                </li>
            </ul>
        </div>

        <!-- Menu Kategori: Transaksi -->
        <div class="space-y-2">
            <div class="flex justify-between items-center {{ request()->routeIs('purchases.*') || request()->routeIs('sales.*') ? 'text-white' : 'text-gray-400' }} px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('transaksi-menu', 'transaksi-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Transaksi</span>
                <svg id="transaksi-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="transaksi-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white transition {{ request()->routeIs('sales.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('sales.index') }}" class="block w-full">Penjualan</a>
                </li>
                <li class="hover:text-white transition {{ request()->routeIs('purchases.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('purchases.index') }}" class="block w-full">Pembelian</a>
                </li>
                <li class="hover:text-white cursor-pointer transition">Pengiriman</li>
            </ul>
        </div>

        <!-- Menu Kategori: Manajemen User -->
        <div class="space-y-2">
            <div class="flex justify-between items-center {{ request()->routeIs('users.*') ? 'text-white' : 'text-gray-400' }} px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('user-menu', 'user-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Manajemen User</span>
                <svg id="user-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="user-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white transition {{ request()->routeIs('users.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('users.index') }}" class="block w-full">Daftar User</a>
                </li>
            </ul>
        </div>

        <!-- Menu Kategori: Data Cabang -->
        <div class="space-y-2">
            <div class="flex justify-between items-center {{ request()->routeIs('wilayah.*') || request()->routeIs('branches.*') || request()->routeIs('suppliers.*') ? 'text-white' : 'text-gray-400' }} px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('cabang-menu', 'cabang-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Data Cabang</span>
                <svg id="cabang-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="cabang-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white transition {{ request()->routeIs('wilayah.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('wilayah.index') }}" class="block w-full">Daftar Wilayah</a>
                </li>
                <li class="hover:text-white transition {{ request()->routeIs('branches.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('branches.index') }}" class="block w-full">Daftar Cabang</a>
                </li>
                <li class="hover:text-white cursor-pointer transition">Stok Cabang</li>
                <li class="hover:text-white transition {{ request()->routeIs('suppliers.*') ? 'text-[#B4F481] font-semibold' : '' }}">
                    <a href="{{ route('suppliers.index') }}" class="block w-full">Daftar Supplier</a>
                </li>
            </ul>
        </div>

        <!-- Menu Kategori: Keuangan -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('keuangan-menu', 'keuangan-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Keuangan</span>
                <svg id="keuangan-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="keuangan-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white cursor-pointer transition">Laporan Keuangan</li>
                <li class="hover:text-white cursor-pointer transition">Arus Kas</li>
            </ul>
        </div>
    </nav>

    <!-- Profil & Logout -->
    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center space-x-3 mb-4 cursor-pointer hover:bg-gray-800/50 p-2 rounded-xl transition">
            <div class="w-8 h-8 rounded-full bg-indigo-900 text-indigo-300 border border-indigo-700 flex items-center justify-center font-bold text-xs select-none">
                {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-sm text-white line-clamp-1">{{ Auth::user()->name ?? 'Super Admin' }}</p>
                <p class="text-[9px] text-gray-400 font-semibold tracking-wider uppercase">ADMIN</p>
            </div>
        </div>

        <!-- CSRF Protected Logout Form -->
        <form id="logout-form" action="{{ url('/auth/logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-500 hover:text-red-400 flex items-center space-x-2 text-sm font-semibold w-full p-2 rounded-lg hover:bg-red-500/10 transition cursor-pointer">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Keluar</span>
        </button>
    </div>
</aside>

<!-- Overlay / Backdrop saat sidebar terbuka di layar kecil -->
<div 
    x-show="sidebarOpen" 
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"
    style="display: none;"
></div>
