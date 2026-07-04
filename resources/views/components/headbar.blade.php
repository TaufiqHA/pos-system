<!-- Header -->
<header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 z-10">
    <div class="flex items-center gap-3">
        <!-- Tombol Hamburger -->
        <button @click="sidebarOpen = true" class="lg:hidden text-gray-400 hover:text-white focus:outline-none p-2 rounded-xl border border-gray-800 bg-gray-900/50 hover:bg-gray-800 transition cursor-pointer flex items-center justify-center shadow-lg shadow-black/20" aria-label="Buka Sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <div>
            <h2 class="text-xl font-bold tracking-wide font-display text-white uppercase">@yield('page_title', 'DASHBOARD')</h2>
            <p class="text-xs text-gray-400 mt-0.5">@yield('page_subtitle', 'Sistem Manajemen POS Lucifer')</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <!-- Dynamic Local Time -->
        <span class="text-gray-400 text-xs font-semibold" id="live-clock">
            Sabtu, 4 Juli 2026
        </span>
    </div>
</header>
