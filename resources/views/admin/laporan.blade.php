@extends('layouts.admin')

@section('title', 'Laporan Keuangan - POS')

@section('page_title', 'LAPORAN KEUANGAN')
@section('page_subtitle', 'Analisis profit, omset, dan penjualan barang')

@section('content')
    <!-- 3 Kotak Indikator Utama -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 z-10">
        <!-- Indikator 1: Total Omset -->
        <div class="card p-5 rounded-2xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-[#B4F481] font-bold tracking-wider uppercase">TOTAL OMSET</p>
                <div class="bg-green-950/50 p-1.5 rounded-lg text-[#B4F481] border border-green-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold text-white font-display">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h3>
        </div>

        <!-- Indikator 2: Total Keuntungan (Estimasi) -->
        <div class="card p-5 rounded-2xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-[#B4F481] font-bold tracking-wider uppercase">TOTAL KEUNTUNGAN (ESTIMASI)</p>
                <div class="bg-[#B4F481]/10 p-1.5 rounded-lg text-[#B4F481] border border-[#B4F481]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold text-[#B4F481] font-display">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</h3>
        </div>

        <!-- Indikator 3: Barang Terjual -->
        <div class="card p-5 rounded-2xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-[#B4F481] font-bold tracking-wider uppercase">BARANG TERJUAL</p>
                <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold text-blue-400 font-display">{{ number_format($barangTerjual, 0, ',', '.') }} Unit</h3>
        </div>
    </div>

    <!-- Area Chart Grafik -->
    <div class="card p-6 rounded-2xl border-gray-700 z-10 shadow-lg shadow-black/15 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">GRAFIK PENJUALAN</h3>
            </div>
        </div>
        <!-- CANVAS FOR CHART.JS -->
        <div class="h-80 w-full relative">
            <canvas id="laporanSalesChart"></canvas>
        </div>
    </div>

    <!-- Bottom Row: 2 Tabel (Produk Terlaris & Detail Transaksi Terakhir) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 z-10 mb-8">
        <!-- PRODUK TERLARIS -->
        <div class="card p-6 rounded-2xl shadow-xl">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white uppercase">PRODUK TERLARIS</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                            <th class="pb-3 text-left">PRODUK</th>
                            <th class="pb-3 text-center">TERJUAL</th>
                            <th class="pb-3 text-right">OMSET</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                        @forelse($produkTerlaris as $item)
                            <tr class="hover:bg-gray-800/30 transition">
                                <td class="py-4 text-left font-semibold text-white uppercase">{{ $item->product_name }}</td>
                                <td class="py-4 text-center font-semibold text-gray-400">{{ $item->total_terjual }}</td>
                                <td class="py-4 text-right font-bold text-[#B4F481]">Rp {{ number_format($item->total_omset, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-500">Belum ada data penjualan produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DETAIL TRANSAKSI TERAKHIR -->
        <div class="card p-6 rounded-2xl shadow-xl">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white uppercase">DETAIL TRANSAKSI TERAKHIR</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                            <th class="pb-3 text-left">TANGGAL</th>
                            <th class="pb-3 text-center">INVOICE</th>
                            <th class="pb-3 text-right">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                        @forelse($transaksiTerakhir as $sale)
                            <tr class="hover:bg-gray-800/30 transition">
                                <td class="py-4 text-left text-gray-400">
                                    {{ $sale->date ? $sale->date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-4 text-center font-semibold text-white font-mono">{{ $sale->invoice }}</td>
                                <td class="py-4 text-right font-bold text-white">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-500">Belum ada transaksi terakhir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Area Line Chart initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('laporanSalesChart').getContext('2d');

            // Create a gorgeous gradient fill for the area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(180, 244, 129, 0.45)');
            gradient.addColorStop(0.6, 'rgba(180, 244, 129, 0.15)');
            gradient.addColorStop(1, 'rgba(180, 244, 129, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Omset Penjualan',
                        data: {!! json_encode($chartValues) !!},
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
                                label: function (context) {
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
                                callback: function (value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000) + 'jt';
                                    }
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
