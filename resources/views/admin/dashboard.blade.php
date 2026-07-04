<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans for UI, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for beautiful analytics graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkBg: '#0B1120',
                        sidebarBg: '#111827',
                        cardBg: '#1F2937',
                        accentGreen: '#B4F481',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #0B1120; 
            color: #FFFFFF; 
        }
        .sidebar { 
            background-color: #111827; 
        }
        .card { 
            background-color: #1F2937; 
            border: 1px solid #374151; 
        }
        /* Custom scrollbar for sidebar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4B5563;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm font-sans antialiased">

    <!-- ================= 1. SIDEBAR KIRI ================= -->
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

    <!-- ================= 2. MAIN CONTENT ================= -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto p-8 relative">
        
        <!-- Background Glow Blobs -->
        <div class="absolute top-10 right-10 w-96 h-96 bg-[#B4F481]/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 z-10">
            <div>
                <h2 class="text-xl font-bold tracking-wide font-display text-white">DASHBOARD</h2>
                <p class="text-xs text-gray-400 mt-0.5">Sistem Manajemen POS Lucifer</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Dynamic Local Time -->
                <span class="text-gray-400 text-xs font-semibold" id="live-clock">
                    Sabtu, 4 Juli 2026
                </span>
                
            </div>
        </header>

        <!-- Quick Action Buttons -->
        <div class="flex justify-end gap-3 mb-6 z-10">
            <button class="px-5 py-2 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition">
                PERMINTAAN PO
            </button>
            <button class="px-5 py-2 rounded-full bg-[#B4F481] text-black font-bold text-xs tracking-wider hover:bg-[#a0dc72] transition flex items-center space-x-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>UPLOAD PRODUK BARU</span>
            </button>
        </div>

        <!-- 4 Kotak Indikator Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 z-10">
            <!-- Indikator 1: Omset Penjualan -->
            <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
                <div class="flex justify-between items-start mb-3">
                    <p class="text-[10px] text-gray-400 font-bold tracking-wider">OMSET PENJUALAN</p>
                    <div class="bg-green-950/50 p-1.5 rounded-lg text-green-400 border border-green-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-white font-display">Rp 110.000</h3>
                <p class="text-xs"><span class="text-green-400 font-bold">+14.2%</span> <span class="text-gray-500">Dari bulan lalu</span></p>
            </div>
            
            <!-- Indikator 2: Hutang Supplier -->
            <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
                <div class="flex justify-between items-start mb-3">
                    <p class="text-[10px] text-gray-400 font-bold tracking-wider">HUTANG (KE SUPPLIER)</p>
                    <div class="bg-yellow-950/50 p-1.5 rounded-lg text-yellow-500 border border-yellow-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-yellow-500 font-display">Rp 0</h3>
                <p class="text-xs"><span class="text-yellow-500 font-bold">Penting</span> <span class="text-gray-500">Bulan ini</span></p>
            </div>

            <!-- Indikator 3: Hutang Cabang -->
            <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
                <div class="flex justify-between items-start mb-3">
                    <p class="text-[10px] text-gray-400 font-bold tracking-wider">HUTANG (DARI CABANG)</p>
                    <div class="bg-red-950/50 p-1.5 rounded-lg text-red-400 border border-red-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-red-400 font-display">Rp 55.000</h3>
                <p class="text-xs"><span class="text-red-500 font-bold">Net Hutang</span> <span class="text-gray-500">Sisa saldo</span></p>
            </div>

            <!-- Indikator 4: Total SKU -->
            <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
                <div class="flex justify-between items-start mb-3">
                    <p class="text-[10px] text-gray-400 font-bold tracking-wider">TOTAL SKU PRODUK</p>
                    <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-blue-400 font-display">1 Item</h3>
                <p class="text-xs"><span class="text-blue-500 font-bold">5 Unit</span> <span class="text-gray-500">Siap jual</span></p>
            </div>
        </div>

        <!-- Area Chart Grafik -->
        <div class="card p-6 rounded-xl mb-8 border-gray-700 z-10 shadow-lg shadow-black/15">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-1 h-5 bg-[#B4F481] rounded-full"></div>
                    <h3 class="font-bold text-sm tracking-wide font-display text-white">TREN OMSET PENJUALAN BULANAN</h3>
                </div>
                <span class="text-[9px] text-[#B4F481] font-bold tracking-widest border border-green-900 bg-green-950/40 px-2.5 py-1 rounded-md">UPDATE OTOMATIS</span>
            </div>
            <!-- CANVAS FOR CHART.JS -->
            <div class="h-64 w-full relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Navigasi & Manajemen Sistem Bawah -->
        <div class="z-10">
            <div class="flex items-center space-x-3 mb-5">
                <div class="w-1.5 h-5 bg-white rounded-full"></div>
                <h3 class="font-bold text-sm tracking-widest font-display text-white">NAVIGASI & MANAJEMEN SISTEM</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Box 1: Produk & Stok -->
                <div class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                    <div class="pr-2">
                        <p class="text-[9px] text-[#B4F481] font-bold mb-1 tracking-widest uppercase">Produk</p>
                        <h4 class="font-bold text-sm mb-1 text-white group-hover:text-[#B4F481] transition-colors font-display">PRODUK & STOK</h4>
                        <p class="text-[10px] text-gray-400 leading-snug">Kelola katalog produk, minimal stok & import.</p>
                    </div>
                    <div class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" class="w-6 h-6 invert" alt="Icon">
                    </div>
                </div>

                <!-- Box 2: Transaksi -->
                <div class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                    <div class="pr-2">
                        <p class="text-[9px] text-blue-400 font-bold mb-1 tracking-widest uppercase">Kasir</p>
                        <h4 class="font-bold text-sm mb-1 text-white group-hover:text-blue-400 transition-colors font-display">TRANSAKSI</h4>
                        <p class="text-[10px] text-gray-400 leading-snug">Input transaksi penjualan, cetak struk, dan pantau kasir.</p>
                    </div>
                    <div class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" class="w-6 h-6 invert" alt="Icon">
                    </div>
                </div>

                <!-- Box 3: Data Cabang -->
                <div class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                    <div class="pr-2">
                        <p class="text-[9px] text-purple-400 font-bold mb-1 tracking-widest uppercase">Cabang</p>
                        <h4 class="font-bold text-sm mb-1 text-white group-hover:text-purple-400 transition-colors font-display">DATA CABANG</h4>
                        <p class="text-[10px] text-gray-400 leading-snug">Kelola lokasi toko cabang, log aktivitas & karyawan.</p>
                    </div>
                    <div class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" class="w-6 h-6 invert" alt="Icon">
                    </div>
                </div>

                <!-- Box 4: Keuangan -->
                <div class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                    <div class="pr-2">
                        <p class="text-[9px] text-yellow-400 font-bold mb-1 tracking-widest uppercase">Laporan</p>
                        <h4 class="font-bold text-sm mb-1 text-white group-hover:text-yellow-400 transition-colors font-display">KEUANGAN</h4>
                        <p class="text-[10px] text-gray-400 leading-snug">Monitor laba rugi bulanan, setoran cash, dan pengeluaran.</p>
                    </div>
                    <div class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <img src="https://cdn-icons-png.flaticon.com/512/2916/2916315.png" class="w-6 h-6 invert" alt="Icon">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Interactive script for sidebar dropdowns and dynamic clock -->
    <script>
        function toggleDropdown(menuId, iconId) {
            const menu = document.getElementById(menuId);
            const icon = document.getElementById(iconId);
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Live Clock Indonesian Localized
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            const dayName = days[now.getDay()];
            const dayNum = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const dateStr = `${dayName}, ${dayNum} ${monthName} ${year}`;
            const timeStr = `${hours}:${minutes}:${seconds}`;
            
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                clockEl.textContent = dateStr;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Chart.js Area Line Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            // Create a gorgeous gradient fill for the area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(180, 244, 129, 0.45)');
            gradient.addColorStop(0.6, 'rgba(180, 244, 129, 0.15)');
            gradient.addColorStop(1, 'rgba(180, 244, 129, 0.00)');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Omset Penjualan',
                        data: [45000, 60000, 52000, 88000, 71000, 95000, 110000],
                        borderColor: '#B4F481',
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#B4F481',
                        pointBorderColor: '#0B1120',
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#B4F481',
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            borderColor: '#374151',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(55, 65, 81, 0.15)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(55, 65, 81, 0.15)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                },
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return 'Rp ' + (value / 1000) + 'k';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
