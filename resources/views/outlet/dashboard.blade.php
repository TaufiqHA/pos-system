@extends('layouts.outlet')

@section('title', 'Dashboard Outlet - POS')
@section('page_title', 'DASHBOARD OUTLET')
@section('page_subtitle', 'Sistem Informasi & POS Outlet ' . (Auth::user()->name ?? 'Lucifer'))

@section('content')
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
        <button onclick="openPoModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition cursor-pointer">
            BUAT ORDER
        </button>
        <button onclick="openDeliveryModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-blue-400 text-blue-400 font-bold text-xs tracking-wider hover:bg-blue-400 hover:text-black transition cursor-pointer">
            STATUS PENGIRIMAN
        </button>
    </div>

    <!-- 2 Kotak Indikator Utama Outlet -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 z-10">
        <!-- Indikator 1: Total Belanja -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">TOTAL BELANJA</p>
                <div class="bg-green-950/50 p-1.5 rounded-lg text-green-400 border border-green-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white font-display">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</h3>
            <p class="text-xs"><span class="text-green-400 font-bold">Akumulasi</span> <span class="text-gray-500">Seluruh transaksi belanja outlet</span></p>
        </div>

        <!-- Indikator 2: Order Selesai -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">ORDER SELESAI</p>
                <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-blue-400 font-display">{{ $totalOrder }} Order</h3>
            <p class="text-xs"><span class="text-blue-500 font-bold">Total</span> <span class="text-gray-500">Order yang telah dilakukan</span></p>
        </div>
    </div>

    <!-- Area Chart & New Product Banner -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 z-10">
        <!-- Area Chart Grafik Tren Penjualan Outlet -->
        <div class="card p-6 rounded-xl border-gray-700 shadow-lg shadow-black/15 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-1 h-5 bg-[#B4F481] rounded-full"></div>
                    <h3 class="font-bold text-sm tracking-wide font-display text-white">TREN TOTAL BELANJA OUTLET BULANAN</h3>
                </div>
                <span
                    class="text-[9px] text-[#B4F481] font-bold tracking-widest border border-green-900 bg-green-950/40 px-2.5 py-1 rounded-md">LIVE UPDATE</span>
            </div>
            <!-- CANVAS FOR CHART.JS -->
            <div class="h-64 w-full relative">
                <canvas id="salesChartOutlet"></canvas>
            </div>
        </div>

        <!-- Banner Produk Baru -->
        <div id="upcoming-product-banner" onclick="handleBannerClick()" class="card p-0 rounded-xl border-gray-700 shadow-lg shadow-black/15 overflow-hidden relative group min-h-[320px] cursor-pointer">
            <!-- Loading indicator -->
            <div id="banner-loading" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 z-20">
                <span class="text-xs text-gray-400">Loading...</span>
            </div>

            <!-- Slides Container -->
            <div id="banner-slides-container" class="absolute inset-0 w-full h-full z-0">
                <!-- Slides will be inserted here dynamically -->
            </div>

            <!-- Dot Indicators -->
            <div id="banner-dots" class="absolute bottom-4 left-0 right-0 flex justify-center gap-1.5 z-10">
                <!-- Dots will be inserted here dynamically -->
            </div>

            <!-- Header Badge -->
            <div class="absolute top-4 left-4 z-10 bg-black/60 backdrop-blur-md border border-gray-850 px-3 py-1 rounded-full text-[9px] font-bold tracking-widest text-[#B4F481] uppercase">
                Produk yang Akan Rilis
            </div>
        </div>
    </div>

    <!-- Navigasi & Manajemen Sistem Bawah -->
    <div class="z-10">
        <div class="flex items-center space-x-3 mb-5">
            <div class="w-1.5 h-5 bg-white rounded-full"></div>
            <h3 class="font-bold text-sm tracking-widest font-display text-white">MODUL UTAMA OUTLET</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Box 1: Order Barang -->
            <a href="{{ route('outlet.order') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-[#B4F481] font-bold mb-1 tracking-widest uppercase">Stok</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-[#B4F481] transition-colors font-display">
                        ORDER BARANG</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Buat dan monitoring pengajuan PO stok barang ke Cabang.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 2: Riwayat Transaksi -->
            <a href="{{ route('outlet.history') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-purple-400 font-bold mb-1 tracking-widest uppercase">History</p>
                    <h4
                        class="font-bold text-sm mb-1 text-white group-hover:text-purple-400 transition-colors font-display">
                        RIWAYAT</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Cek log penjualan harian dan riwayat mutasi stok outlet.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>
        </div>
    </div>

    <!-- ================= MODAL BOX: BUAT ORDER (PO) ================= -->
    <div id="po-modal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
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
                <h3 class="text-base font-bold tracking-wide font-display text-white">Buat Order Barang</h3>
                <p class="text-[11px] text-gray-400 mt-1">Formulir pengajuan order/PO barang ke cabang penyuplai</p>
            </div>

            <!-- Form Input UI -->
            <form id="po-form" action="{{ route('purchase-orders.store') }}" method="POST"
                onsubmit="return validatePoForm(event)" class="space-y-6 text-xs">
                @csrf
                <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id ?? '' }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id ?? '' }}">
                <input type="hidden" name="status" value="Pending">
                <div id="po-hidden-items-container"></div>

                <!-- Grid 2 Kolom Atas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Cabang (Readonly) -->
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Cabang Penyuplai *</label>
                        <input type="text" readonly value="{{ auth()->user()->branch->name ?? 'Cabang' }}"
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
                    <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase">ITEM ORDER</h4>
                    <div class="flex flex-col sm:flex-row gap-2 items-start">
                        <div class="flex-1 flex gap-3 items-start w-full">
                            <!-- Image Preview of Selected Product -->
                            <div class="w-12 h-12 rounded-xl bg-gray-900 border border-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden mt-1">
                                <img id="po-product-image-preview" src="" alt="" class="w-full h-full object-cover hidden">
                                <div id="po-product-image-placeholder" class="text-gray-600 text-[10px] uppercase font-bold">Image</div>
                            </div>
                            <div class="flex-1 min-w-0">
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
                                            $stock = $product->productStocks->first()?->stock ?? 0;
                                        @endphp
                                        <option value="{{ $product->id }}"
                                            data-sku="{{ $product->sku }}"
                                            data-price="{{ $product->buy_price }}"
                                            data-pusat-price="{{ $pusatPrice }}"
                                            data-name="{{ $product->name }}"
                                            data-stock="{{ $stock }}"
                                            data-image="{{ $product->image ?? '' }}"
                                            data-wholesale='{{ json_encode($wholesaleData) }}'>
                                            {{ $product->name }} (SKU: {{ $product->sku }}) - Rp
                                            {{ number_format($pusatPrice, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="po-stock-info" class="hidden mt-1 text-[11px] text-[#B4F481] font-semibold">
                                    Stok Cabang: <span id="po-stock-info-val">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-full sm:w-24">
                            <input type="number" id="po-qty-input" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                            <div id="po-qty-warning" class="hidden mt-1 text-[10px] text-red-400 font-semibold leading-tight">
                                Melebihi stok cabang!
                            </div>
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
                                <th class="py-3 px-4 font-semibold w-16 text-center">Gambar</th>
                                <th class="py-3 px-4 font-semibold">Produk</th>
                                <th class="py-3 px-4 font-semibold">Qty</th>
                                <th class="py-3 px-4 font-semibold">Harga</th>
                                <th class="py-3 px-4 font-semibold">Subtotal</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="po-items-table-body">
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500 font-medium">Belum ada item ditambahkan</td>
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
                        Kirim Order
                    </button>
                </div>
            </form>
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
                <p class="text-[11px] text-gray-400 mt-1">Daftar pengiriman barang dari cabang ke outlet Anda</p>
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
                                <td colspan="5" class="py-8 text-center text-gray-500 font-medium">Belum ada pengiriman dari
                                    cabang</td>
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
                    Invoice: <span id="detail-invoice" class="font-bold text-white font-mono font-display"></span> |
                    Driver: <span id="detail-driver" class="font-bold text-white font-mono font-display"></span>
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

    <!-- Chart.js Area Line Chart initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChartOutlet').getContext('2d');

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
                        label: 'Total Belanja',
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
            function updateStockDisplay() {
                const select = document.getElementById('po-product-select');
                const selectedOption = select.options[select.selectedIndex];
                const qtyInput = document.getElementById('po-qty-input');
                const stockInfo = document.getElementById('po-stock-info');
                const stockInfoVal = document.getElementById('po-stock-info-val');
                const qtyWarning = document.getElementById('po-qty-warning');

                if (selectedOption && selectedOption.value) {
                    const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
                    stockInfoVal.textContent = stock;
                    stockInfo.classList.remove('hidden');

                    const qty = parseInt(qtyInput.value) || 0;
                    if (qty > stock) {
                        qtyWarning.classList.remove('hidden');
                        qtyInput.classList.add('border-red-500');
                        qtyInput.classList.remove('focus:border-[#B4F481]');
                    } else {
                        qtyWarning.classList.add('hidden');
                        qtyInput.classList.remove('border-red-500');
                        qtyInput.classList.add('focus:border-[#B4F481]');
                    }
                } else {
                    stockInfo.classList.add('hidden');
                    qtyWarning.classList.add('hidden');
                    qtyInput.classList.remove('border-red-500');
                    qtyInput.classList.add('focus:border-[#B4F481]');
                }
            }

            document.getElementById('po-product-select').addEventListener('change', function() {
                updateWholesalePricesSelect();
                updatePriceBasedOnQty();
                updateStockDisplay();

                const selectedOption = this.options[this.selectedIndex];
                const imgPreview = document.getElementById('po-product-image-preview');
                const imgPlaceholder = document.getElementById('po-product-image-placeholder');

                if (selectedOption && selectedOption.value) {
                    const imageSrc = selectedOption.getAttribute('data-image');
                    if (imageSrc) {
                        imgPreview.src = imageSrc;
                        imgPreview.classList.remove('hidden');
                        imgPlaceholder.classList.add('hidden');
                    } else {
                        imgPreview.src = '';
                        imgPreview.classList.add('hidden');
                        imgPlaceholder.classList.remove('hidden');
                    }
                } else {
                    imgPreview.src = '';
                    imgPreview.classList.add('hidden');
                    imgPlaceholder.classList.remove('hidden');
                }
            });
            document.getElementById('po-qty-input').addEventListener('input', function() {
                updatePriceBasedOnQty();
                updateStockDisplay();
            });
            document.getElementById('po-use-wholesale').addEventListener('change', function() {
                updateWholesalePricesSelect();
                updatePriceBasedOnQty();
                updateStockDisplay();
            });
            document.getElementById('po-wholesale-price-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const qtyInput = document.getElementById('po-qty-input');
                if (selectedOption && selectedOption.value) {
                    qtyInput.value = parseInt(selectedOption.value);
                }
                updatePriceBasedOnQty();
                updateStockDisplay();
            });
        });

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
            const imgPreview = document.getElementById('po-product-image-preview');
            const imgPlaceholder = document.getElementById('po-product-image-placeholder');
            if (imgPreview) {
                imgPreview.src = '';
                imgPreview.classList.add('hidden');
            }
            if (imgPlaceholder) {
                imgPlaceholder.classList.remove('hidden');
            }
            if (typeof updateStockDisplay === 'function') {
                updateStockDisplay();
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

                let appliedPrice = defaultPrice;
                let activeWholesale = null;

                if (useWholesale) {
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

                if (activeWholesale) {
                    wholesaleSelect.value = activeWholesale.min_qty;
                } else {
                    wholesaleSelect.value = "";
                }

                if (wholesalePrices.length > 0 && useWholesale) {
                    infoDiv.classList.remove('hidden');
                    let infoHtml = '<strong>Tersedia Harga Grosir:</strong> ';
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

            const useWholesaleCheckbox = document.getElementById('po-use-wholesale');
            const useWholesale = useWholesaleCheckbox.checked;

            const productId = selectedOption.value;
            const name = selectedOption.getAttribute('data-name');
            const sku = selectedOption.getAttribute('data-sku');

            const image = selectedOption.getAttribute('data-image') || '';

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

            const existingIndex = poItems.findIndex(item => item.product_id === productId);
            if (existingIndex > -1) {
                const newQty = poItems[existingIndex].qty + qty;
                poItems[existingIndex].qty = newQty;

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
                    image: image,
                    is_wholesale: isWholesaleApplied
                });
            }

            select.value = '';
            qtyInput.value = '';
            priceInput.value = '';
            useWholesaleCheckbox.checked = false;
            updateWholesalePricesSelect();
            document.getElementById('po-wholesale-info').classList.add('hidden');
            document.getElementById('po-stock-info').classList.add('hidden');
            document.getElementById('po-qty-warning').classList.add('hidden');
            qtyInput.classList.remove('border-red-500');
            qtyInput.classList.add('focus:border-[#B4F481]');

            // Reset image preview
            const imgPreview = document.getElementById('po-product-image-preview');
            const imgPlaceholder = document.getElementById('po-product-image-placeholder');
            if (imgPreview) {
                imgPreview.src = '';
                imgPreview.classList.add('hidden');
            }
            if (imgPlaceholder) {
                imgPlaceholder.classList.remove('hidden');
            }

            updatePoTable();
        }

        function removePoItem(index) {
            poItems.splice(index, 1);
            updatePoTable();
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
                
                let imgHtml = '';
                if (item.image) {
                    imgHtml = `<img src="${item.image}" alt="${item.name}" class="w-10 h-10 rounded-lg object-cover border border-gray-800/80 shadow-md mx-auto">`;
                } else {
                    imgHtml = `<div class="w-10 h-10 bg-gray-800 border border-gray-700 rounded-lg flex items-center justify-center text-gray-500 text-[9px] font-semibold uppercase mx-auto">No Img</div>`;
                }

                tr.innerHTML = `
                    <td class="py-3 px-4">
                        <div class="flex justify-center">${imgHtml}</div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-bold text-white">${item.name}</div>
                        <div class="text-[10px] text-gray-400">SKU: ${item.sku}</div>
                    </td>
                    <td class="py-3 px-4 text-white">${item.qty}</td>
                    <td class="py-3 px-4 text-white">Rp ${item.price.toLocaleString('id-ID')}${badge}</td>
                    <td class="py-3 px-4 text-white">Rp ${itemSubtotal.toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-center">
                        <button type="button" onclick="removePoItem(${index})" class="text-red-400 hover:text-red-300 font-bold transition">Hapus</button>
                    </td>
                `;
                tbody.appendChild(tr);

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
        let upcomingProductsData = [];
        let currentSlideIndex = 0;
        let slideInterval = null;

        function handleBannerClick() {
            if (upcomingProductsData.length > 0 && upcomingProductsData[currentSlideIndex]) {
                openDetailUpcomingProductModalOutlet(upcomingProductsData[currentSlideIndex].id);
            }
        }

        function initUpcomingProductsBanner() {
            fetch('/outlet/upcoming-products', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                upcomingProductsData = data;
                const slidesContainer = document.getElementById('banner-slides-container');
                const dotsContainer = document.getElementById('banner-dots');
                const loadingEl = document.getElementById('banner-loading');

                if (loadingEl) loadingEl.classList.add('hidden');

                if (!data || data.length === 0) {
                    slidesContainer.innerHTML = `
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 bg-gray-900 text-center">
                            <svg class="w-10 h-10 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h4 class="font-bold text-xs text-white">Nantikan Produk Baru</h4>
                            <p class="text-[10px] text-gray-400 mt-1 max-w-xs leading-normal">Rencana produk baru menarik sedang dipersiapkan oleh Pusat.</p>
                        </div>
                    `;
                    return;
                }

                // Render Slides
                slidesContainer.innerHTML = '';
                dotsContainer.innerHTML = '';

                data.forEach((item, index) => {
                    const slide = document.createElement('div');
                    slide.id = `banner-slide-${index}`;
                    slide.className = `absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out ${index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'}`;
                    
                    const imgHtml = item.image 
                        ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">`
                        : `<div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 to-gray-950 p-6 text-center">
                                <svg class="w-12 h-12 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-bold text-xs text-white">${escapeHtml(item.name)}</span>
                           </div>`;
                    
                    slide.innerHTML = imgHtml;
                    slidesContainer.appendChild(slide);

                    // Create dot indicator
                    if (data.length > 1) {
                        const dot = document.createElement('button');
                        dot.className = `w-2 h-2 rounded-full transition-all duration-300 ${index === 0 ? 'bg-[#B4F481] scale-125' : 'bg-gray-600 hover:bg-gray-400'}`;
                        dot.onclick = (e) => {
                            e.stopPropagation();
                            showSlide(index);
                        };
                        dotsContainer.appendChild(dot);
                    }
                });

                // Start autoplay if there's more than 1 slide
                if (data.length > 1) {
                    startAutoplay();
                }
            })
            .catch(error => {
                console.error('Error fetching upcoming products for banner:', error);
                const slidesContainer = document.getElementById('banner-slides-container');
                const loadingEl = document.getElementById('banner-loading');
                if (loadingEl) loadingEl.classList.add('hidden');
                slidesContainer.innerHTML = `
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-900 text-red-400 text-xs">
                        Gagal memuat data.
                    </div>
                `;
            });
        }

        function showSlide(index) {
            if (upcomingProductsData.length <= 1) return;

            stopAutoplay();

            const oldSlide = document.getElementById(`banner-slide-${currentSlideIndex}`);
            const newSlide = document.getElementById(`banner-slide-${index}`);
            const dots = document.getElementById('banner-dots').children;

            if (oldSlide && newSlide) {
                oldSlide.classList.remove('opacity-100', 'z-10');
                oldSlide.classList.add('opacity-0', 'z-0');

                newSlide.classList.remove('opacity-0', 'z-0');
                newSlide.classList.add('opacity-100', 'z-10');

                if (dots[currentSlideIndex]) {
                    dots[currentSlideIndex].className = 'w-2 h-2 rounded-full bg-gray-600 hover:bg-gray-400 transition-all duration-300';
                }
                if (dots[index]) {
                    dots[index].className = 'w-2 h-2 rounded-full bg-[#B4F481] scale-125 transition-all duration-300';
                }

                currentSlideIndex = index;
            }

            startAutoplay();
        }

        function startAutoplay() {
            slideInterval = setInterval(() => {
                let nextIndex = (currentSlideIndex + 1) % upcomingProductsData.length;
                showSlide(nextIndex);
            }, 4000);
        }

        function stopAutoplay() {
            if (slideInterval) {
                clearInterval(slideInterval);
            }
        }

        function openDetailUpcomingProductModalOutlet(id) {
            fetch(`/outlet/upcoming-products`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const item = data.find(p => p.id === id);
                if (!item) return;

                const img = document.getElementById('detail-upcoming-image-outlet');
                if (item.image) {
                    img.src = item.image;
                    img.classList.remove('hidden');
                } else {
                    img.src = '';
                    img.classList.add('hidden');
                }

                document.getElementById('detail-upcoming-name-view-outlet').innerText = item.name;
                document.getElementById('detail-upcoming-description-view-outlet').innerText = item.description || '-';

                document.getElementById('detail-upcoming-product-modal-outlet').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching detail:', error);
                alert('Gagal mengambil rincian detail produk.');
            });
        }

        function closeDetailUpcomingProductModalOutlet() {
            document.getElementById('detail-upcoming-product-modal-outlet').classList.add('hidden');
        }

        // Helper escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Initialize upcoming products banner on load
        document.addEventListener('DOMContentLoaded', function () {
            initUpcomingProductsBanner();
        });
    </script>

    <!-- ================= MODAL BOX: DETAIL UPCOMING PRODUCT (OUTLET) ================= -->
    <div id="detail-upcoming-product-modal-outlet"
        class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-850 font-sans">
            <button onclick="closeDetailUpcomingProductModalOutlet()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Produk yang akan rilis</h3>
                <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap rencana produk baru dari Pusat</p>
            </div>
            
            <div class="space-y-4 text-xs text-gray-300">
                <!-- Large Image Preview -->
                <div class="flex justify-center bg-gray-950 p-4 rounded-xl border border-gray-800">
                    <img id="detail-upcoming-image-outlet" src="" alt="Upcoming Product Image" class="max-h-48 rounded-lg object-contain">
                </div>

                <div class="space-y-1">
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider font-display">Nama Produk</span>
                    <p id="detail-upcoming-name-view-outlet" class="text-sm font-bold text-white"></p>
                </div>

                <div class="space-y-1">
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider font-display">Deskripsi</span>
                    <p id="detail-upcoming-description-view-outlet" class="leading-relaxed bg-gray-900/60 p-3 rounded-xl border border-gray-850 whitespace-pre-line"></p>
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="button" onclick="closeDetailUpcomingProductModalOutlet()"
                    class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endsection
