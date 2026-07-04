<!-- ================= SIDEBAR KIRI ================= -->
<aside class="sidebar w-64 flex flex-col justify-between h-full border-r border-gray-800 flex-shrink-0">
    <!-- Logo & Nama Perusahaan -->
    <div class="p-6">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-tr from-[#B4F481] to-green-400 rounded-lg flex items-center justify-center shadow-lg shadow-green-500/20">
                <span class="text-black font-black text-sm tracking-tighter">L</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-green-400 tracking-wider font-display">LUCIFER</h1>
                <p class="text-[9px] text-gray-400 tracking-widest uppercase font-semibold">Kantor Pusat</p>
            </div>
        </div>
    </div>

    <!-- Navigasi Menu -->
    <nav class="flex-1 px-4 space-y-4 overflow-y-auto pb-4">
        <!-- Menu Aktif (Dashboard) -->
        <div class="bg-indigo-950/60 text-white rounded-xl p-3 flex items-center space-x-3 border border-indigo-700/50 cursor-pointer shadow-md shadow-indigo-950/30">
            <svg class="w-5 h-5 text-[#B4F481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span class="font-medium text-sm">Dashboard</span>
        </div>

        <!-- Menu Kategori: Produk & Stok -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('produk-menu', 'produk-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Produk & Stok</span>
                <svg id="produk-icon" class="w-3 h-3 transform transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="produk-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs transition-all duration-200">
                <li class="hover:text-white cursor-pointer transition">Daftar Produk</li>
                <li class="hover:text-white cursor-pointer transition">Monitoring Stok</li>
            </ul>
        </div>

        <!-- Menu Kategori: Transaksi -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('transaksi-menu', 'transaksi-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Transaksi</span>
                <svg id="transaksi-icon" class="w-3 h-3 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="transaksi-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs hidden transition-all duration-200">
                <li class="hover:text-white cursor-pointer transition">Kasir / Penjualan</li>
                <li class="hover:text-white cursor-pointer transition">Riwayat Transaksi</li>
            </ul>
        </div>

        <!-- Menu Kategori: Data Cabang -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('cabang-menu', 'cabang-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Data Cabang</span>
                <svg id="cabang-icon" class="w-3 h-3 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="cabang-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs hidden transition-all duration-200">
                <li class="hover:text-white cursor-pointer transition">Kelola Cabang</li>
                <li class="hover:text-white cursor-pointer transition">Stok Cabang</li>
            </ul>
        </div>

        <!-- Menu Kategori: Keuangan -->
        <div class="space-y-2">
            <div class="flex justify-between items-center text-gray-400 px-2 cursor-pointer hover:text-white transition" onclick="toggleDropdown('keuangan-menu', 'keuangan-icon')">
                <span class="text-[10px] font-bold tracking-wider uppercase">Keuangan</span>
                <svg id="keuangan-icon" class="w-3 h-3 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul id="keuangan-menu" class="text-gray-400 space-y-2 ml-4 border-l border-gray-800 pl-4 text-xs hidden transition-all duration-200">
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
