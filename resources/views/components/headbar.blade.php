<!-- Header -->
<header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 z-10">
    <div>
        <h2 class="text-xl font-bold tracking-wide font-display text-white uppercase">@yield('page_title', 'DASHBOARD')</h2>
        <p class="text-xs text-gray-400 mt-0.5">@yield('page_subtitle', 'Sistem Manajemen POS Lucifer')</p>
    </div>
    <div class="flex items-center gap-3">
        <!-- Dynamic Local Time -->
        <span class="text-gray-400 text-xs font-semibold" id="live-clock">
            Sabtu, 4 Juli 2026
        </span>
    </div>
</header>
