@extends('layouts.cabang')

@section('title', 'Dashboard Cabang - POS')
@section('page_title', 'DASHBOARD CABANG')
@section('page_subtitle', 'Sistem Informasi & POS Cabang Lucifer')

@section('content')
    @php
        $selectedPoId = request('outlet_po_id');
        $selectedPo = $selectedPoId ? $outletPurchaseOrders->firstWhere('id', $selectedPoId) : $outletPurchaseOrders->first();
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;
        $showOutletPoModal = request()->has('show_outlet_po') || request()->has('outlet_po_id');
    @endphp
    @if (session('success'))
        <div id="success-alert"
            class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-end gap-3 mb-6 z-10">
        <button onclick="openPermintaanPoModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-yellow-400 text-yellow-400 font-bold text-xs tracking-wider hover:bg-yellow-400 hover:text-black transition cursor-pointer relative">
            PERMINTAAN PO OUTLET
            @php
                $pendingCount = $outletPurchaseOrders->where('status', 'Pending')->count();
            @endphp
            @if($pendingCount > 0)
                <span
                    class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse border border-[#0B1120]">
                    {{ $pendingCount }}
                </span>
            @endif
        </button>
        <button onclick="openPoModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition cursor-pointer">
            PO KE PUSAT
        </button>
        <button onclick="openDeliveryModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-blue-400 text-blue-400 font-bold text-xs tracking-wider hover:bg-blue-400 hover:text-black transition cursor-pointer">
            STATUS PENGIRIMAN
        </button>
    </div>

    <!-- 3 Kotak Indikator Utama Cabang -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 z-10">
        <!-- Indikator 1: Omset Penjualan -->
        <div class="card p-5 rounded-xl hover:border-green-500/40 transition-all duration-300 shadow-lg shadow-black/20 group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">OMSET PENJUALAN</p>
                <div class="bg-green-950/50 p-1.5 rounded-lg text-green-400 border border-green-800/40 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white font-display">Rp {{ number_format($omsetPenjualan, 0, ',', '.') }}</h3>
            <p class="text-xs">
                <span class="text-green-400 font-bold">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</span>
                <span class="text-gray-500">Hari ini</span>
            </p>
        </div>

        <!-- Indikator 2: Hutang ke Pusat -->
        <div class="card p-5 rounded-xl hover:border-yellow-500/40 transition-all duration-300 shadow-lg shadow-black/20 group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">HUTANG KE PUSAT</p>
                <div class="bg-yellow-950/50 p-1.5 rounded-lg text-yellow-500 border border-yellow-800/40 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-yellow-500 font-display">Rp {{ number_format($hutangPusat, 0, ',', '.') }}</h3>
            <p class="text-xs">
                <span class="text-yellow-500 font-bold">{{ $hutangPusatCount }}</span>
                <span class="text-gray-500">Transaksi belum lunas</span>
            </p>
        </div>

        <!-- Indikator 3: Total Produk -->
        <div class="card p-5 rounded-xl hover:border-blue-500/40 transition-all duration-300 shadow-lg shadow-black/20 group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">TOTAL PRODUK</p>
                <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white font-display">{{ $totalProduk }} SKU</h3>
            <p class="text-xs">
                <span class="text-blue-400 font-bold">{{ number_format($totalStok, 0, ',', '.') }} Unit</span>
                <span class="text-gray-500">Stok tersedia</span>
            </p>
        </div>
    </div>

    <!-- Area Chart Grafik Tren Penjualan Cabang -->
    <div class="card p-6 rounded-xl mb-8 border-gray-700 z-10 shadow-lg shadow-black/15">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-1 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">TREN OMSET PENJUALAN CABANG BULANAN</h3>
            </div>
            <span
                class="text-[9px] text-[#B4F481] font-bold tracking-widest border border-green-900 bg-green-950/40 px-2.5 py-1 rounded-md">LIVE
                UPDATE</span>
        </div>
        <!-- CANVAS FOR CHART.JS -->
        <div class="h-64 w-full relative">
            <canvas id="salesChartCabang"></canvas>
        </div>
    </div>

    <!-- Navigasi & Manajemen Sistem Bawah -->
    <div class="z-10">
        <div class="flex items-center space-x-3 mb-5">
            <div class="w-1.5 h-5 bg-white rounded-full"></div>
            <h3 class="font-bold text-sm tracking-widest font-display text-white">MODUL UTAMA CABANG</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Box 1: Produk -->
            <a href="{{ route('cabang.monitoring-stok') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-[#B4F481] font-bold mb-1 tracking-widest uppercase">Katalog</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-[#B4F481] transition-colors font-display">
                        PRODUK</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Monitoring stok outlet, cek riwayat, dan update harga
                        jual.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 2: Penjualan -->
            <a href="{{ route('cabang.penjualan') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-blue-400 font-bold mb-1 tracking-widest uppercase">Kasir</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-blue-400 transition-colors font-display">
                        PENJUALAN</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Entri penjualan harian, cetak struk, dan riwayat transaksi.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 3: Daftar PO -->
            <a href="{{ route('purchase-orders.index') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-purple-400 font-bold mb-1 tracking-widest uppercase">Pusat</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-purple-400 transition-colors font-display">
                        DAFTAR PO</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Buat purchase order barang ke pusat dan monitoring status PO.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 4: Hutang -->
            <a href="{{ route('cabang.hutang') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-yellow-400 font-bold mb-1 tracking-widest uppercase">Keuangan</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-yellow-400 transition-colors font-display">
                        HUTANG</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Laporan sisa hutang cabang ke pusat dan riwayat pembayarannya.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/2916/2916315.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>
        </div>
    </div>

    <!-- Chart.js Area Line Chart initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChartCabang').getContext('2d');

            // Create a gorgeous gradient fill for the area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(180, 244, 129, 0.45)');
            gradient.addColorStop(0.6, 'rgba(180, 244, 129, 0.15)');
            gradient.addColorStop(1, 'rgba(180, 244, 129, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Omset Cabang',
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

    <!-- ================= MODAL BOX: PO KE PUSAT ================= -->
    <div id="po-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-4xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Tombol Close (X) -->
            <button onclick="closePoModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Buat PO ke Pusat</h3>
                <p class="text-[11px] text-gray-400 mt-1">Formulir pengajuan Purchase Order barang ke gudang pusat</p>
            </div>

            <!-- Form Input UI -->
            <form id="po-form" action="{{ route('purchase-orders.store') }}" method="POST"
                onsubmit="return validatePoForm(event)" class="space-y-6 text-xs">
                @csrf
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? '' }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id ?? '' }}">
                <input type="hidden" name="status" value="Pending">
                <div id="po-hidden-items-container"></div>

                <!-- Grid 2 Kolom Atas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Pusat (Readonly) -->
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Pusat *</label>
                        <input type="text" readonly value="{{ auth()->user()->parent->name ?? 'Pusat' }}"
                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                    </div>

                    <!-- Tanggal Transaksi -->
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Tanggal Transaksi *</label>
                        <div class="relative">
                            <input type="text" readonly value="{{ now()->format('d/m/Y, h:i A') }}"
                                class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 pr-10 focus:outline-none cursor-not-allowed">
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Item PO -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase">ITEM PO</h4>
                    <div class="flex flex-col sm:flex-row gap-2 items-start">
                        <div class="flex-1">
                            <select id="po-product-select"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    @php
                                        $wholesaleData = $product->wholesalePrices->sortBy('min_qty')->map(function ($wp) {
                                            return [
                                                'min_qty' => (int) $wp->min_qty,
                                                'price' => (float) $wp->price
                                            ];
                                        })->values()->all();
                                        $pusatPrice = $product->branchPrices->first()?->sell_price ?? $product->sell_price;
                                        $pusatStock = $product->productStocks->first()?->stock ?? 0;
                                    @endphp
                                    <option value="{{ $product->id }}" 
                                        data-sku="{{ $product->sku }}"
                                        data-price="{{ $product->buy_price }}" 
                                        data-pusat-price="{{ $pusatPrice }}"
                                        data-name="{{ $product->name }}"
                                        data-stock="{{ $pusatStock }}"
                                        data-wholesale='{{ json_encode($wholesaleData) }}'>
                                        {{ $product->name }} (SKU: {{ $product->sku }}) - Rp
                                        {{ number_format($pusatPrice, 0, ',', '.') }} (Stok Pusat: {{ $pusatStock }})
                                    </option>
                                @endforeach
                            </select>
                            <p id="po-product-stock-display" class="text-[11px] text-gray-400 mt-1.5 hidden"></p>
                        </div>
                        <div class="w-full sm:w-24">
                            <input type="number" id="po-qty-input" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                            <p id="po-qty-error" class="text-[9px] text-red-400 mt-1.5 hidden leading-tight"></p>
                        </div>
                        <div class="w-full sm:w-28 flex items-center gap-2 bg-gray-900 border border-gray-800 rounded-xl p-3">
                            <input type="checkbox" id="po-use-wholesale"
                                class="w-4 h-4 rounded text-[#B4F481] bg-gray-950 border-gray-800 focus:ring-0 accent-[#B4F481] cursor-pointer">
                            <label for="po-use-wholesale" class="text-gray-300 font-bold cursor-pointer select-none">Grosir</label>
                        </div>
                        <div id="po-wholesale-select-container" class="hidden w-full sm:w-48">
                            <select id="po-wholesale-price-select"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                <option value="">-- Pilih Tingkat Grosir --</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-36">
                            <input type="text" id="po-price-input" placeholder="Harga" readonly
                                class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        </div>
                        <button type="button" onclick="addPoItem()"
                            class="bg-[#B4F481] hover:bg-green-400 text-black font-bold p-3 rounded-xl transition w-full sm:w-12 flex items-center justify-center cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <div id="po-wholesale-info" class="hidden text-[11px] text-[#B4F481] bg-[#B4F481]/10 border border-[#B4F481]/30 p-3 rounded-xl flex flex-wrap gap-2 items-center"></div>
                </div>

                <!-- Table Item PO -->
                <div class="bg-gray-900/40 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                                <th class="py-3 px-4 font-semibold">Produk</th>
                                <th class="py-3 px-4 font-semibold">Stok Pusat</th>
                                <th class="py-3 px-4 font-semibold">Qty</th>
                                <th class="py-3 px-4 font-semibold">Harga</th>
                                <th class="py-3 px-4 font-semibold">Subtotal</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="po-items-table-body">
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500 font-medium">Belum ada item
                                    ditambahkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Kolom 1 (Subtotal, Diskon, Pajak) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Subtotal *</label>
                        <input type="text" id="po-subtotal-display" value="0" readonly
                            class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        <input type="hidden" id="po-subtotal" name="subtotal" value="0">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Diskon</label>
                        <input type="number" id="po-discount-input" name="discount" value="0" min="0"
                            oninput="calculateGrandTotal()"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Pajak</label>
                        <input type="number" id="po-tax-input" name="tax" value="0" min="0" oninput="calculateGrandTotal()"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                    </div>
                </div>

                <!-- Ringkasan Kolom 2 (Grand Total) -->
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Grand Total *</label>
                    <input type="text" id="po-grand-total-display" value="0" readonly
                        class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                    <input type="hidden" id="po-grand-total" name="grand_total" value="0">
                </div>
                <input type="hidden" id="po-payment-method" name="payment_method" value="KREDIT">

                <!-- Notes / Catatan -->
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Catatan / Notes</label>
                    <textarea id="po-notes-input" name="user_notes" placeholder="Masukkan catatan tambahan jika ada..."
                        rows="3"
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]"></textarea>
                </div>

                <!-- Tombol Action -->
                <div
                    class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-850">
                    <button type="button" onclick="closePoModal()"
                        class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-6 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-po"
                        class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-8 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                        Kirim PO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: PERMINTAAN PO OUTLET ================= -->
    <div id="permintaan-po-modal"
        class="fixed inset-0 z-50 @if(!$showOutletPoModal) hidden @endif bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-6xl w-full p-0 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Tombol Close (X) -->
            <button onclick="closePermintaanPoModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer z-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="p-6 border-b border-gray-800 bg-gray-900/40">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Daftar Permintaan PO Outlet</h3>
                <p class="text-[11px] text-gray-400 mt-1">Daftar pengajuan Purchase Order dari outlet yang terhubung</p>
            </div>

            <!-- Modal Content - Split Screen Grid -->
            <div class="flex-1 flex overflow-hidden min-h-0">
                <!-- Side Kiri: Daftar PO -->
                <div class="w-1/3 border-r border-gray-800 overflow-y-auto bg-gray-950/40 p-4 space-y-3">
                    <h4 class="text-[10px] font-bold tracking-wider text-gray-500 uppercase px-2">Pilih Permintaan PO</h4>
                    <div class="space-y-2" id="po-list-container">
                        @forelse($outletPurchaseOrders as $po)
                            @php
                                $notes = json_decode($po->notes, true);
                                $grandTotal = $notes['grand_total'] ?? 0;
                            @endphp
                            <a href="?outlet_po_id={{ $po->id }}&show_outlet_po=1" id="po-item-{{ $po->id }}"
                                class="p-3.5 rounded-xl border {{ $selectedPo && $selectedPo->id === $po->id ? 'bg-gray-850 border-gray-600' : 'bg-gray-900/60 border-gray-800' }} hover:bg-gray-850 hover:border-gray-700 cursor-pointer transition flex flex-col gap-2 group block">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="font-bold text-xs text-white font-mono group-hover:text-[#B4F481] transition-colors">{{ $po->po_number }}</span>
                                    <!-- Status Badge -->
                                    <span id="po-badge-{{ $po->id }}" class="px-2 py-0.5 rounded-full text-[9px] font-bold border 
                                                @if($po->status === 'Pending') bg-yellow-500/10 text-yellow-500 border-yellow-500/20
                                                @elseif($po->status === 'Approved') bg-blue-500/10 text-blue-400 border-blue-500/20
                                                @elseif($po->status === 'Completed') bg-green-500/10 text-green-400 border-green-500/20
                                                @elseif($po->status === 'Rejected') bg-red-500/10 text-red-400 border-red-500/20
                                                @else bg-gray-500/10 text-gray-400 border-gray-500/20 @endif">
                                        {{ $po->status }}
                                    </span>
                                </div>
                                <div class="flex flex-col text-[11px] text-gray-400 leading-snug">
                                    <div class="font-semibold text-gray-300">
                                        {{ $po->outlet->name ?? ($po->user->name ?? 'Outlet') }}</div>
                                    <div>{{ $po->created_at->format('d M Y, H:i') }}</div>
                                </div>
                                <div class="text-xs font-bold text-[#B4F481] mt-1">
                                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                </div>
                            </a>
                        @empty
                            <div class="py-8 text-center text-gray-500 text-xs">
                                Tidak ada permintaan PO masuk
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Side Kanan: Detail Konten (Field Form PO) -->
                <div class="flex-1 overflow-y-auto p-6 bg-[#1F2937]/30 flex flex-col justify-between"
                    id="po-detail-container">
                    @if(!$selectedPo)
                        <!-- Placeholder when no PO selected -->
                        <div id="po-placeholder" class="flex-1 flex flex-col items-center justify-center text-center p-8">
                            <div
                                class="w-16 h-16 rounded-2xl bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-500 mb-4 animate-bounce">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-white text-sm">Pilih Permintaan PO</h4>
                            <p class="text-xs text-gray-400 max-w-xs mt-1">Silakan pilih salah satu purchase order di sebelah
                                kiri untuk melihat rincian detail pengajuan.</p>
                        </div>
                    @else
                        <!-- Detail View -->
                        <form id="approval-form" action="{{ route('purchase-orders.update', $selectedPo->id) }}" method="POST"
                            class="flex-1 flex flex-col justify-between text-xs space-y-6">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="po_number" value="{{ $selectedPo->po_number }}">
                            <input type="hidden" name="outlet_id" value="{{ $selectedPo->outlet_id }}">
                            <input type="hidden" name="user_id" value="{{ $selectedPo->user_id }}">
                            <input type="hidden" id="form-status" name="status" value="">

                            <!-- Flat notes inputs for controller auto-serialization -->
                            <input type="hidden" name="user_notes" value="{{ $selectedPoNotes['user_notes'] ?? '' }}">
                            <input type="hidden" name="subtotal" value="{{ $selectedPoNotes['subtotal'] ?? 0 }}">
                            <input type="hidden" name="discount" value="{{ $selectedPoNotes['discount'] ?? 0 }}">
                            <input type="hidden" name="tax" value="{{ $selectedPoNotes['tax'] ?? 0 }}">
                            <input type="hidden" name="grand_total" value="{{ $selectedPoNotes['grand_total'] ?? 0 }}">
                            @foreach(($selectedPoNotes['items'] ?? []) as $index => $item)
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] }}">
                                <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] }}">
                                <input type="hidden" name="items[{{ $index }}][sku]" value="{{ $item['sku'] }}">
                                <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                                <input type="hidden" name="items[{{ $index }}][price]" value="{{ $item['price'] }}">
                            @endforeach

                            <!-- Grid 2 Kolom Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Outlet (Readonly) -->
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-300">Outlet *</label>
                                    <input type="text" readonly
                                        value="{{ $selectedPo->outlet->name ?? ($selectedPo->user->name ?? 'Outlet') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                </div>

                                <!-- Tanggal Transaksi -->
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-300">Tanggal Transaksi *</label>
                                    <div class="relative">
                                        <input type="text" readonly
                                            value="{{ $selectedPo->created_at->format('d/m/Y, h:i A') }}"
                                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 pr-10 focus:outline-none cursor-not-allowed">
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Table Item PO -->
                            <div class="space-y-3">
                                <h4 class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">ITEM DAFTAR PO</h4>
                                <div class="bg-gray-900/40 rounded-xl overflow-hidden border border-gray-800/80">
                                    <table class="w-full text-left text-gray-300 border-collapse">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                                                <th class="py-3 px-4 font-semibold">Produk</th>
                                                <th class="py-3 px-4 font-semibold text-center">Qty</th>
                                                <th class="py-3 px-4 font-semibold text-right">Harga</th>
                                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($selectedPoNotes['items'] ?? []) as $item)
                                                @php
                                                    $itemSubtotal = $item['qty'] * $item['price'];
                                                @endphp
                                                <tr class="border-b border-gray-800 hover:bg-gray-800/20 transition text-[11px]">
                                                    <td class="py-3 px-4">
                                                        <div class="font-bold text-white">{{ $item['name'] }}</div>
                                                        <div class="text-[10px] text-gray-400">SKU: {{ $item['sku'] }}</div>
                                                    </td>
                                                    <td class="py-3 px-4 text-center text-white">{{ $item['qty'] }}</td>
                                                    <td class="py-3 px-4 text-right text-white">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                                    <td class="py-3 px-4 text-right font-bold text-[#B4F481]">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-500">Tidak ada item dalam PO ini</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Grand Total & Payment Method -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Grand Total *</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($selectedPoNotes['grand_total'] ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-[#B4F481] font-bold rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-350">Metode Pembayaran *</label>
                                    @if($selectedPo->status === 'Pending')
                                        <select name="payment_method"
                                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                            <option value="KREDIT" @if(($selectedPoNotes['payment_method'] ?? 'KREDIT') === 'KREDIT')
                                            selected @endif>KREDIT</option>
                                            <option value="TUNAI" @if(($selectedPoNotes['payment_method'] ?? 'KREDIT') === 'TUNAI')
                                            selected @endif>TUNAI</option>
                                            <option value="TRANSFER" @if(($selectedPoNotes['payment_method'] ?? 'KREDIT') === 'TRANSFER') selected @endif>TRANSFER</option>
                                        </select>
                                    @else
                                        <select disabled
                                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                            <option selected>{{ $selectedPoNotes['payment_method'] ?? 'KREDIT' }}</option>
                                        </select>
                                    @endif
                                </div>
                            </div>

                            <!-- Notes / Catatan -->
                            <div class="space-y-1">
                                <label class="block font-bold text-gray-355">Catatan / Notes Outlet</label>
                                <textarea readonly rows="2"
                                    class="w-full bg-gray-900/60 border border-gray-800 text-gray-300 rounded-xl p-3 cursor-not-allowed focus:outline-none italic">{{ $selectedPoNotes['user_notes'] ?? '-' }}</textarea>
                            </div>

                            <!-- Tombol Action & Persetujuan -->
                            <div
                                class="pt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between border-t border-gray-855 gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-455 font-bold uppercase text-[10px]">Status Saat Ini:</span>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border 
                                                @if($selectedPo->status === 'Pending') bg-yellow-500/10 text-yellow-500 border-yellow-500/20
                                                @elseif($selectedPo->status === 'Approved') bg-blue-500/10 text-blue-400 border-blue-500/20
                                                @elseif($selectedPo->status === 'Completed') bg-green-500/10 text-green-400 border-green-500/20
                                                @elseif($selectedPo->status === 'Rejected') bg-red-500/10 text-red-400 border-red-500/20
                                                @else bg-gray-500/10 text-gray-450 border-gray-500/20 @endif">
                                        {{ $selectedPo->status }}
                                    </span>
                                </div>
                                @if($selectedPo->status === 'Pending')
                                    <div class="flex items-center gap-3 w-full sm:w-auto" id="po-action-buttons">
                                        <button type="button" onclick="submitApprovalForm('Rejected')"
                                            class="w-1/2 sm:w-auto bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                                            Tolak PO
                                        </button>
                                        <button type="button" onclick="submitApprovalForm('Approved')"
                                            class="w-1/2 sm:w-auto bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-8 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                                            Setujui PO
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL BOX: STATUS PENGIRIMAN ================= -->
    <div id="delivery-modal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-4xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Tombol Close (X) -->
            <button onclick="closeDeliveryModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Status Pengiriman</h3>
                <p class="text-[11px] text-gray-400 mt-1">Daftar pengiriman barang dari pusat ke cabang Anda</p>
            </div>

            <!-- Table Daftar Pengiriman -->
            <div class="bg-gray-900/40 rounded-xl overflow-hidden">
                <table class="w-full text-left text-gray-300 border-collapse">
                    <thead>
                        <tr
                            class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                            <th class="py-3 px-4 font-semibold">Invoice Penjualan</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold">Waktu Kirim</th>
                            <th class="py-3 px-4 font-semibold">Waktu Diterima</th>
                            <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr class="border-b border-gray-800 hover:bg-gray-800/30 transition">
                                <td class="py-3 px-4 font-bold text-white font-mono">
                                    {{ $delivery->sale?->invoice ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold
                                                @if($delivery->status === 'DITERIMA') bg-green-500/20 text-green-400
                                                @elseif($delivery->status === 'DIKIRIM') bg-blue-500/20 text-blue-400
                                                @elseif($delivery->status === 'PENDING') bg-yellow-500/20 text-yellow-400
                                                @else bg-red-500/20 text-red-400 @endif">
                                        {{ strtoupper($delivery->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-400 font-medium">
                                    {{ $delivery->sent_at ? $delivery->sent_at->format('d-m-Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-gray-400 font-medium">
                                    {{ $delivery->received_at ? $delivery->received_at->format('d-m-Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($delivery->status === 'DIKIRIM')
                                        <button onclick='openDeliveryDetailModal(@json($delivery))'
                                            class="bg-green-500 hover:bg-green-400 text-black font-bold px-3 py-1 rounded-full text-[10px] transition cursor-pointer">
                                            Terima Barang
                                        </button>
                                    @else
                                        <span class="text-gray-500 font-medium text-[10px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500 font-medium">Belum ada pengiriman dari
                                    pusat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= MODAL BOX: DETAIL PENGIRIMAN ================= -->
    <div id="delivery-detail-modal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Tombol Close (X) -->
            <button onclick="closeDeliveryDetailModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Pesanan & Pengiriman</h3>
                <p class="text-[11px] text-gray-400 mt-1">
                    Invoice: <span id="detail-invoice" class="font-bold text-white font-mono"></span> |
                    Driver: <span id="detail-driver" class="font-bold text-white"></span>
                </p>
            </div>

            <!-- Table Detail Items -->
            <div class="bg-gray-900/40 rounded-xl overflow-hidden mb-6">
                <table class="w-full text-left text-gray-300 border-collapse">
                    <thead>
                        <tr
                            class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                            <th class="py-3 px-4 font-semibold w-12">No</th>
                            <th class="py-3 px-4 font-semibold">Produk</th>
                            <th class="py-3 px-4 font-semibold">SKU</th>
                            <th class="py-3 px-4 font-semibold">Qty</th>
                            <th class="py-3 px-4 font-semibold">Harga</th>
                            <th class="py-3 px-4 font-semibold">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail-items-body">
                        <!-- Dynamic items -->
                    </tbody>
                </table>
            </div>

            <!-- Total & Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-850">
                <div class="text-left w-full sm:w-auto">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Grand Total</p>
                    <p id="detail-grand-total" class="text-lg font-bold text-[#B4F481] font-display"></p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeDeliveryDetailModal()"
                        class="text-gray-400 hover:text-white font-semibold py-2 px-5 rounded-xl hover:bg-gray-800 transition cursor-pointer text-xs">
                        Kembali
                    </button>
                    <button type="button" id="btn-confirm-receive"
                        class="bg-green-500 hover:bg-green-400 text-black font-bold py-2 px-6 rounded-xl transition shadow-lg shadow-green-500/20 cursor-pointer text-xs">
                        Terima Barang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka dan menutup Modal PO
        let poItems = [];

        function openPoModal() {
            document.getElementById('po-modal').classList.remove('hidden');
        }

        function closePoModal() {
            document.getElementById('po-modal').classList.add('hidden');
            const select = document.getElementById('po-product-select');
            if (select) select.value = '';
            const qtyInput = document.getElementById('po-qty-input');
            if (qtyInput) qtyInput.value = '';
            const stockDisplay = document.getElementById('po-product-stock-display');
            if (stockDisplay) {
                stockDisplay.classList.add('hidden');
                stockDisplay.innerHTML = '';
            }
            if (typeof checkQtyStock === 'function') {
                checkQtyStock();
            }
        }

        function openDeliveryModal() {
            document.getElementById('delivery-modal').classList.remove('hidden');
        }

        function closeDeliveryModal() {
            document.getElementById('delivery-modal').classList.add('hidden');
        }

        function openDeliveryDetailModal(delivery) {
            closeDeliveryModal();

            document.getElementById('detail-invoice').innerText = delivery.sale?.invoice || '-';
            document.getElementById('detail-driver').innerText = delivery.driver_name || 'Belum Ditentukan';

            const tbody = document.getElementById('detail-items-body');
            tbody.innerHTML = '';

            let total = 0;
            const items = delivery.sale?.sales_items || delivery.sale?.salesItems || [];
            items.forEach((item, index) => {
                const subtotal = item.qty * item.price;
                total += subtotal;

                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-800 text-[11px]';
                tr.innerHTML = `
                        <td class="py-3 px-4 text-gray-400 font-semibold">${index + 1}</td>
                        <td class="py-3 px-4 font-bold text-white">${item.product_name}</td>
                        <td class="py-3 px-4 text-gray-300 font-mono">${item.sku}</td>
                        <td class="py-3 px-4 text-white">${item.qty} ${item.unit || 'pcs'}</td>
                        <td class="py-3 px-4 text-white">Rp ${item.price.toLocaleString('id-ID')}</td>
                        <td class="py-3 px-4 font-bold text-[#B4F481]">Rp ${subtotal.toLocaleString('id-ID')}</td>
                    `;
                tbody.appendChild(tr);
            });

            document.getElementById('detail-grand-total').innerText = 'Rp ' + total.toLocaleString('id-ID');

            document.getElementById('btn-confirm-receive').setAttribute('onclick', `executeReceive('${delivery.id}')`);

            document.getElementById('delivery-detail-modal').classList.remove('hidden');
        }

        function closeDeliveryDetailModal() {
            document.getElementById('delivery-detail-modal').classList.add('hidden');
            openDeliveryModal();
        }

        async function executeReceive(deliveryId) {
            if (!confirm('Apakah Anda yakin barang sudah diterima?')) return;

            try {
                const response = await fetch(`/auth/deliveries/${deliveryId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'DITERIMA',
                        received_at: new Date().toISOString()
                    })
                });

                if (response.ok) {
                    alert('Status pengiriman berhasil diperbarui menjadi DITERIMA.');
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert('Gagal memperbarui status: ' + (data.message || 'Error'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi.');
            }
        }

        function formatCurrency(value) {
            return Math.round(value).toLocaleString('id-ID');
        }

        function parseCurrency(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        function updateWholesalePricesSelect() {
            const select = document.getElementById('po-product-select');
            const selectedOption = select.options[select.selectedIndex];
            const useWholesaleCheckbox = document.getElementById('po-use-wholesale');
            const selectContainer = document.getElementById('po-wholesale-select-container');
            const wholesaleSelect = document.getElementById('po-wholesale-price-select');

            // Reset select options
            wholesaleSelect.innerHTML = '<option value="">-- Pilih Tingkat Grosir --</option>';

            if (selectedOption && selectedOption.value && useWholesaleCheckbox.checked) {
                const wholesaleStr = selectedOption.getAttribute('data-wholesale');
                let wholesalePrices = [];
                try {
                    wholesalePrices = JSON.parse(wholesaleStr) || [];
                } catch(e) {}

                if (wholesalePrices.length > 0) {
                    selectContainer.classList.remove('hidden');
                    wholesalePrices.sort((a, b) => a.min_qty - b.min_qty);
                    wholesalePrices.forEach(wp => {
                        const opt = document.createElement('option');
                        opt.value = wp.min_qty;
                        opt.setAttribute('data-price', wp.price);
                        opt.textContent = `Min. ${wp.min_qty} - Rp ${formatCurrency(wp.price)}`;
                        wholesaleSelect.appendChild(opt);
                    });
                } else {
                    selectContainer.classList.add('hidden');
                }
            } else {
                selectContainer.classList.add('hidden');
            }
        }

        // Auto prefill price when product changes or qty changes, checking wholesale prices
        function updatePriceBasedOnQty() {
            const select = document.getElementById('po-product-select');
            const selectedOption = select.options[select.selectedIndex];
            const qtyInput = document.getElementById('po-qty-input');
            const priceInput = document.getElementById('po-price-input');
            const infoDiv = document.getElementById('po-wholesale-info');
            const useWholesaleCheckbox = document.getElementById('po-use-wholesale');
            const wholesaleSelect = document.getElementById('po-wholesale-price-select');

            if (selectedOption && selectedOption.value) {
                const defaultPrice = parseFloat(selectedOption.getAttribute('data-pusat-price')) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                const useWholesale = useWholesaleCheckbox.checked;

                const wholesaleStr = selectedOption.getAttribute('data-wholesale');
                let wholesalePrices = [];
                try {
                    wholesalePrices = JSON.parse(wholesaleStr) || [];
                } catch(e) {
                    wholesalePrices = [];
                }

                // Find if any wholesale price matches the qty
                let appliedPrice = defaultPrice;
                let activeWholesale = null;

                if (useWholesale) {
                    // Sort descending to check from highest threshold
                    wholesalePrices.sort((a, b) => b.min_qty - a.min_qty);

                    for (let wp of wholesalePrices) {
                        if (qty >= wp.min_qty) {
                            appliedPrice = wp.price;
                            activeWholesale = wp;
                            break;
                        }
                    }
                }

                priceInput.value = formatCurrency(appliedPrice);

                // Synchronize the wholesale select value
                if (activeWholesale) {
                    wholesaleSelect.value = activeWholesale.min_qty;
                } else {
                    wholesaleSelect.value = "";
                }

                // Update info div text
                if (wholesalePrices.length > 0 && useWholesale) {
                    infoDiv.classList.remove('hidden');
                    let infoHtml = '<strong>Tersedia Harga Grosir:</strong> ';
                    // Sort ascending for display
                    const displayWp = [...wholesalePrices].sort((a, b) => a.min_qty - b.min_qty);
                    const wpTexts = displayWp.map(wp => {
                        const isApplied = activeWholesale && activeWholesale.min_qty === wp.min_qty;
                        const style = isApplied ? 'text-black font-extrabold bg-[#B4F481] px-2 py-0.5 rounded shadow' : 'text-[#B4F481] opacity-75';
                        return `<span class="${style}">Min. ${wp.min_qty} = Rp ${formatCurrency(wp.price)}</span>`;
                    });
                    infoHtml += wpTexts.join(' | ');
                    infoDiv.innerHTML = infoHtml;
                } else {
                    infoDiv.classList.add('hidden');
                    infoDiv.innerHTML = '';
                }
            } else {
                priceInput.value = '';
                infoDiv.classList.add('hidden');
                infoDiv.innerHTML = '';
                wholesaleSelect.value = "";
            }
        }

        function checkQtyStock() {
            const select = document.getElementById('po-product-select');
            const selectedOption = select.options[select.selectedIndex];
            const qtyInput = document.getElementById('po-qty-input');
            const errorText = document.getElementById('po-qty-error');
            const btnAdd = document.querySelector('button[onclick="addPoItem()"]');

            if (selectedOption && selectedOption.value) {
                const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                const productId = selectedOption.value;

                // Calculate total including already added items of this product
                let existingQty = 0;
                const existingIndex = poItems.findIndex(item => item.product_id === productId);
                if (existingIndex > -1) {
                    existingQty = poItems[existingIndex].qty;
                }

                const totalQty = existingQty + qty;

                if (totalQty > stock) {
                    errorText.innerText = `Maks ${stock - existingQty} pcs`;
                    errorText.classList.remove('hidden');
                    qtyInput.classList.add('border-red-500');
                    qtyInput.classList.remove('focus:border-[#B4F481]');
                    if (btnAdd) {
                        btnAdd.disabled = true;
                        btnAdd.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                    return false;
                }
            }

            errorText.classList.add('hidden');
            qtyInput.classList.remove('border-red-500');
            qtyInput.classList.add('focus:border-[#B4F481]');
            if (btnAdd) {
                btnAdd.disabled = false;
                btnAdd.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            return true;
        }

        document.getElementById('po-product-select').addEventListener('change', function() {
            updateWholesalePricesSelect();
            updatePriceBasedOnQty();

            // Update stock display text
            const selectedOption = this.options[this.selectedIndex];
            const stockDisplay = document.getElementById('po-product-stock-display');
            if (selectedOption && selectedOption.value) {
                const stock = selectedOption.getAttribute('data-stock') || 0;
                stockDisplay.innerHTML = `Stok Gudang Pusat: <span class="font-bold text-[#B4F481]">${stock} pcs</span>`;
                stockDisplay.classList.remove('hidden');
            } else {
                stockDisplay.classList.add('hidden');
                stockDisplay.innerHTML = '';
            }
            checkQtyStock();
        });
        document.getElementById('po-qty-input').addEventListener('input', function() {
            updatePriceBasedOnQty();
            checkQtyStock();
        });
        document.getElementById('po-use-wholesale').addEventListener('change', function() {
            updateWholesalePricesSelect();
            updatePriceBasedOnQty();
            checkQtyStock();
        });
        document.getElementById('po-wholesale-price-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const qtyInput = document.getElementById('po-qty-input');
            if (selectedOption && selectedOption.value) {
                qtyInput.value = parseInt(selectedOption.value);
            }
            updatePriceBasedOnQty();
            checkQtyStock();
        });

        function addPoItem() {
            const select = document.getElementById('po-product-select');
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                alert('Silakan pilih produk terlebih dahulu.');
                return;
            }

            const qtyInput = document.getElementById('po-qty-input');
            const qty = parseInt(qtyInput.value);
            if (!qty || qty <= 0) {
                alert('Silakan masukkan jumlah (Qty) yang valid.');
                return;
            }

            const priceInput = document.getElementById('po-price-input');
            const price = parseCurrency(priceInput.value);
            if (isNaN(price) || price < 0) {
                alert('Silakan masukkan harga yang valid.');
                return;
            }

            if (!checkQtyStock()) {
                return;
            }

            const useWholesaleCheckbox = document.getElementById('po-use-wholesale');
            const useWholesale = useWholesaleCheckbox.checked;

            const productId = selectedOption.value;
            const name = selectedOption.getAttribute('data-name');
            const sku = selectedOption.getAttribute('data-sku');
            const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

            // Find if the final applied price is indeed a wholesale price
            const defaultPrice = parseFloat(selectedOption.getAttribute('data-pusat-price')) || 0;
            const wholesaleStr = selectedOption.getAttribute('data-wholesale');
            let wholesalePrices = [];
            try {
                wholesalePrices = JSON.parse(wholesaleStr) || [];
            } catch(e) {}
            
            let isWholesaleApplied = false;
            if (useWholesale) {
                wholesalePrices.sort((a, b) => b.min_qty - a.min_qty);
                for (let wp of wholesalePrices) {
                    if (qty >= wp.min_qty) {
                        isWholesaleApplied = true;
                        break;
                    }
                }
            }

            // Check if product already added
            const existingIndex = poItems.findIndex(item => item.product_id === productId);
            if (existingIndex > -1) {
                const newQty = poItems[existingIndex].qty + qty;
                poItems[existingIndex].qty = newQty;

                // Update price for the new quantity based on wholesale prices
                let appliedPrice = defaultPrice;
                let newIsWholesaleApplied = false;
                if (useWholesale) {
                    wholesalePrices.sort((a, b) => b.min_qty - a.min_qty);
                    for (let wp of wholesalePrices) {
                        if (newQty >= wp.min_qty) {
                            appliedPrice = wp.price;
                            newIsWholesaleApplied = true;
                            break;
                        }
                    }
                }
                poItems[existingIndex].price = appliedPrice;
                poItems[existingIndex].is_wholesale = newIsWholesaleApplied;
            } else {
                poItems.push({
                    product_id: productId,
                    name: name,
                    sku: sku,
                    qty: qty,
                    price: price,
                    stock: stock,
                    is_wholesale: isWholesaleApplied
                });
            }

            // Reset inputs
            select.value = '';
            qtyInput.value = '';
            priceInput.value = '';
            useWholesaleCheckbox.checked = false; // reset checkbox to default unchecked
            updateWholesalePricesSelect();
            document.getElementById('po-wholesale-info').classList.add('hidden');
            const stockDisplay = document.getElementById('po-product-stock-display');
            stockDisplay.classList.add('hidden');
            stockDisplay.innerHTML = '';
            checkQtyStock();

            updatePoTable();
        }

        function removePoItem(index) {
            poItems.splice(index, 1);
            updatePoTable();
            checkQtyStock();
        }

        function updatePoTable() {
            const tbody = document.getElementById('po-items-table-body');
            tbody.innerHTML = '';

            const hiddenContainer = document.getElementById('po-hidden-items-container');
            hiddenContainer.innerHTML = '';

            if (poItems.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-gray-500 font-medium">Belum ada item ditambahkan</td></tr>`;
                document.getElementById('po-subtotal').value = 0;
                document.getElementById('po-subtotal-display').value = '0';
                document.getElementById('po-grand-total').value = 0;
                document.getElementById('po-grand-total-display').value = '0';
                return;
            }

            let subtotal = 0;
            poItems.forEach((item, index) => {
                const itemSubtotal = item.qty * item.price;
                subtotal += itemSubtotal;

                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-800';
                const badge = item.is_wholesale ? ' <span class="ml-1.5 inline-block bg-green-950/20 text-[#B4F481] border border-green-800/50 py-0.5 px-2 rounded-full text-[9px] font-semibold">Grosir</span>' : '';
                tr.innerHTML = `
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-white">${item.name}</div>
                                            <div class="text-[10px] text-gray-400">SKU: ${item.sku}</div>
                                        </td>
                                        <td class="py-3 px-4 text-white">${item.stock} pcs</td>
                                        <td class="py-3 px-4 text-white">${item.qty}</td>
                                        <td class="py-3 px-4 text-white">Rp ${item.price.toLocaleString('id-ID')}${badge}</td>
                                        <td class="py-3 px-4 text-white">Rp ${itemSubtotal.toLocaleString('id-ID')}</td>
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" onclick="removePoItem(${index})" class="text-red-400 hover:text-red-300 font-bold transition">Hapus</button>
                                        </td>
                                    `;
                tbody.appendChild(tr);

                // Append hidden inputs for standard form submission
                hiddenContainer.innerHTML += `
                                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                        <input type="hidden" name="items[${index}][name]" value="${item.name}">
                                        <input type="hidden" name="items[${index}][sku]" value="${item.sku}">
                                        <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                                        <input type="hidden" name="items[${index}][price]" value="${item.price}">
                                        <input type="hidden" name="items[${index}][is_wholesale]" value="${item.is_wholesale ? 1 : 0}">
                                    `;
            });

            document.getElementById('po-subtotal').value = subtotal;
            document.getElementById('po-subtotal-display').value = formatCurrency(subtotal);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const subtotal = parseFloat(document.getElementById('po-subtotal').value) || 0;
            const discount = parseFloat(document.getElementById('po-discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('po-tax-input').value) || 0;

            const grandTotal = Math.max(0, subtotal - discount + tax);
            document.getElementById('po-grand-total').value = grandTotal;
            document.getElementById('po-grand-total-display').value = formatCurrency(grandTotal);
        }

        function validatePoForm(event) {
            if (poItems.length === 0) {
                alert('Silakan tambahkan minimal 1 item PO.');
                event.preventDefault();
                return false;
            }
            return true;
        }
        function openPermintaanPoModal() {
            document.getElementById('permintaan-po-modal').classList.remove('hidden');
        }

        function closePermintaanPoModal() {
            window.location.href = "{{ route('cabang.dashboard') }}";
        }

        function submitApprovalForm(status) {
            if (!confirm(`Apakah Anda yakin ingin menandai PO ini sebagai ${status === 'Approved' ? 'SETUJU' : 'DITOLAK'}?`)) {
                return;
            }
            document.getElementById('form-status').value = status;
            document.getElementById('approval-form').submit();
        }
    </script>
@endsection