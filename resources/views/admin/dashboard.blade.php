@extends('layouts.admin')

@section('title', 'Dashboard - POS')

@section('content')
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

    <!-- Chart.js Area Line Chart initialization -->
    <script>
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
@endsection
