@extends('layouts.cabang')

@section('title', 'Penjualan Cabang - POS')
@section('page_title', 'PENJUALAN CABANG')
@section('page_subtitle', 'Riwayat Penjualan Cabang')

@section('content')
    @php
        $selectedPoId = request('outlet_po_id');
        $selectedPo = $selectedPoId ? $outletPurchaseOrders->firstWhere('id', $selectedPoId) : $outletPurchaseOrders->first();
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;
        $showOutletPoModal = request()->has('show_outlet_po') || request()->has('outlet_po_id');
    @endphp

    @if(session('success'))
        <div id="success-alert"
            class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-6 rounded-2xl shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <div></div>
            <div class="flex items-center gap-3">
                <button onclick="openPermintaanPoModal()"
                    class="w-full sm:w-auto justify-center border border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 cursor-pointer relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Daftar PO dari Outlet
                    @php
                        $pendingCount = $outletPurchaseOrders->where('status', 'Pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse border border-[#0B1120]">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </button>
                <button onclick="openCreateModal()"
                    class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Penjualan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                        <th class="pb-3 pl-4 pr-4">Tanggal</th>
                        <th class="pb-3 px-4">Invoice</th>
                        <th class="pb-3 px-4">Outlet</th>
                        <th class="pb-3 px-4 text-right">Total</th>
                        <th class="pb-3 px-4 text-center">Status</th>
                        <th class="pb-3 pl-4 pr-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="py-4 pl-4 pr-4 whitespace-nowrap">{{ $sale->date->format('d M Y') }}</td>
                            <td class="py-4 px-4">{{ $sale->invoice }}</td>
                            <td class="py-4 px-4">{{ $sale->outlet->name ?? '—' }}</td>
                            <td class="py-4 px-4 text-right font-semibold">Rp
                                {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-semibold
                                            @if(in_array(strtolower($sale->status), ['completed', 'lunas'])) bg-green-500/20 text-green-400
                                            @elseif(in_array(strtolower($sale->status), ['pending', 'belum bayar'])) bg-yellow-500/20 text-yellow-400
                                            @else bg-red-500/20 text-red-400 @endif">
                                    {{ strtoupper($sale->status) }}
                                </span>
                            </td>
                            <td class="py-4 pl-4 pr-4 text-center">
                                <button onclick="openDetailModal('{{ $sale->id }}')"
                                    class="text-indigo-400 hover:text-indigo-200 transition-colors text-sm cursor-pointer bg-transparent border-0 p-0">Detail</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">Tidak ada data penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL BOX: TAMBAH PENJUALAN ================= -->
    <div id="create-modal"
        class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Penjualan Baru</h3>
                <p class="text-[11px] text-gray-400 mt-1">Buat data transaksi penjualan baru</p>
            </div>
            <form action="{{ route('sales.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="create-date" class="block font-bold text-gray-300">Tanggal Transaksi <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="date" id="create-date"
                            value="{{ old('date') ?? now()->format('Y-m-d\TH:i') }}"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                        @error('date')<p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-1">
                        <label for="create-outlet" class="block font-bold text-gray-300">Outlet Tujuan</label>
                        <select name="outlet_id" id="create-outlet"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                            <option value="">-- Pilih Outlet --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}</option>
                            @endforeach
                        </select>
                        @error('outlet_id')<p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                <div class="space-y-2 mb-3">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <select id="create-item-product" onchange="updateProductPrice('create')"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    @php
                                        $branchPrice = $product->branchPrices->first();
                                        $sellPrice = $branchPrice ? $branchPrice->sell_price : $product->sell_price;
                                        $wholesalePrices = $product->wholesalePrices->where('branch_id', auth()->user()->branch_id)->values();
                                    @endphp
                                    <option value="{{ $product->id }}" 
                                        data-price="{{ $sellPrice }}"
                                        data-wholesale='{{ json_encode($wholesalePrices) }}'>
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-24">
                            <input type="number" id="create-item-qty" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                        </div>
                        <div class="w-full sm:w-28 flex items-center gap-2 bg-gray-900 border border-gray-800 rounded-xl p-2.5">
                            <input type="checkbox" id="create-use-wholesale"
                                class="w-4 h-4 rounded text-[#B4F481] bg-gray-950 border-gray-800 focus:ring-0 accent-[#B4F481] cursor-pointer">
                            <label for="create-use-wholesale" class="text-gray-300 font-bold cursor-pointer select-none">Grosir</label>
                        </div>
                        <div id="create-wholesale-select-container" class="hidden w-full sm:w-48">
                            <select id="create-wholesale-price-select"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                                <option value="">-- Pilih Tingkat Grosir --</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-32 flex gap-2">
                            <input type="text" id="create-item-price" placeholder="Harga"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-2.5 focus:outline-none cursor-not-allowed"
                                readonly>
                            <button type="button" onclick="addItem('create')"
                                class="bg-[#B4F481] hover:bg-green-400 text-black px-4 rounded-xl font-bold cursor-pointer">+</button>
                        </div>
                    </div>
                    <div id="create-wholesale-info" class="hidden text-[11px] text-[#B4F481] bg-[#B4F481]/10 border border-[#B4F481]/30 p-2.5 rounded-xl flex flex-wrap gap-2 items-center"></div>
                </div>
                <div class="overflow-x-auto max-h-[150px] overflow-y-auto mb-4 border border-gray-800 rounded-xl">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-900 text-gray-400 font-bold border-b border-gray-800">
                                <th class="p-2">Produk</th>
                                <th class="p-2 text-center">Qty</th>
                                <th class="p-2 text-right">Harga</th>
                                <th class="p-2 text-right">Subtotal</th>
                                <th class="p-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="create-items-body" class="divide-y divide-gray-800 text-gray-300">
                            <tr id="create-no-items">
                                <td colspan="5" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="create-subtotal-display" class="block font-bold text-gray-300">Subtotal <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="create-subtotal-display" value="{{ old('subtotal') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="subtotal" id="create-subtotal" value="{{ old('subtotal') ?? 0 }}">
                    </div>
                    <div class="space-y-1">
                        <label for="create-discount-display" class="block font-bold text-gray-300">Diskon</label>
                        <input type="text" id="create-discount-display" value="{{ old('discount') ?? 0 }}"
                            oninput="formatRupiahInput(this); updateHiddenVal(this, 'create-discount')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="discount" id="create-discount" value="{{ old('discount') ?? 0 }}">
                    </div>
                    <div class="space-y-1">
                        <label for="create-tax-display" class="block font-bold text-gray-300">Pajak</label>
                        <input type="text" id="create-tax-display" value="{{ old('tax') ?? 0 }}"
                            oninput="formatRupiahInput(this); updateHiddenVal(this, 'create-tax')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="tax" id="create-tax" value="{{ old('tax') ?? 0 }}">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="create-grand_total-display" class="block font-bold text-gray-300">Total <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="create-grand_total-display" value="{{ old('grand_total') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="grand_total" id="create-grand_total"
                            value="{{ old('grand_total') ?? 0 }}">
                    </div>
                    <div class="space-y-1">
                        <label for="create-payment_method" class="block font-bold text-gray-300">Metode Pembayaran <span
                                class="text-red-500">*</span></label>
                        <select name="payment_method" id="create-payment_method"
                            onchange="updateStatusFromPaymentMethod('create')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="KREDIT">KREDIT</option>
                        </select>
                        <input type="hidden" name="status" id="create-status" value="LUNAS">
                    </div>
                </div>
                <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()"
                        class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">Batal</button>
                    <button type="submit"
                        class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">Simpan
                        Penjualan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: DETAIL PENJUALAN ================= -->
    <div id="detail-modal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <button onclick="closeDetailModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Transaksi Penjualan</h3>
                <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap transaksi penjualan</p>
            </div>
            <div class="space-y-4 text-xs">
                <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Invoice</p>
                        <p id="detail-invoice" class="text-white font-mono font-bold mt-0.5 text-xs select-all"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Tanggal</p>
                        <p id="detail-date" class="text-white mt-0.5"></p>
                    </div>

                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Outlet</p>
                        <p id="detail-outlet" class="text-white mt-0.5 font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Status</p>
                        <span id="detail-status"
                            class="inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase"></span>
                    </div>
                </div>

                <!-- Table Detail Items -->
                <div class="bg-gray-900/40 rounded-xl overflow-hidden mb-4 border border-gray-800">
                    <table class="w-full text-left text-gray-300 border-collapse text-[11px]">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 font-bold uppercase bg-gray-900/60">
                                <th class="p-2 w-12">No</th>
                                <th class="p-2">Produk</th>
                                <th class="p-2">SKU</th>
                                <th class="p-2 text-center">Qty</th>
                                <th class="p-2 text-right">Harga</th>
                                <th class="p-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-body" class="divide-y divide-gray-800">
                            <!-- Dynamic items -->
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="bg-gray-900/30 p-4 rounded-xl space-y-2 border border-gray-800">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal</span>
                        <span id="detail-subtotal" class="font-semibold text-white"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Diskon</span>
                        <span id="detail-discount" class="font-semibold text-red-400"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Pajak</span>
                        <span id="detail-tax" class="font-semibold text-blue-400"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-800 pt-2 font-bold">
                        <span class="text-white">Grand Total</span>
                        <span id="detail-grand-total" class="text-sm text-[#B4F481]"></span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button onclick="closeDetailModal()"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
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
                                    <label class="block font-bold text-gray-355">Metode Pembayaran *</label>
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

    <script>
        function openPermintaanPoModal() {
            document.getElementById('permintaan-po-modal').classList.remove('hidden');
        }

        function closePermintaanPoModal() {
            window.location.href = "{{ route('cabang.penjualan') }}";
        }

        function submitApprovalForm(status) {
            if (!confirm(`Apakah Anda yakin ingin menandai PO ini sebagai ${status === 'Approved' ? 'SETUJU' : 'DITOLAK'}?`)) {
                return;
            }
            document.getElementById('form-status').value = status;
            document.getElementById('approval-form').submit();
        }

        function openCreateModal() { document.getElementById('create-modal').classList.remove('hidden'); }
        function closeCreateModal() { document.getElementById('create-modal').classList.add('hidden'); }

        async function openDetailModal(saleId) {
            try {
                const response = await fetch(`/auth/sales/${saleId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Gagal mengambil data penjualan');
                const sale = await response.json();

                document.getElementById('detail-invoice').textContent = sale.invoice;
                document.getElementById('detail-date').textContent = new Date(sale.date).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                document.getElementById('detail-outlet').textContent = sale.outlet ? sale.outlet.name : '—';

                const statusEl = document.getElementById('detail-status');
                statusEl.textContent = sale.status.toUpperCase();
                statusEl.className = 'inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase';
                const statusLower = sale.status.toLowerCase();
                if (statusLower === 'completed' || statusLower === 'lunas') {
                    statusEl.classList.add('bg-green-500/20', 'text-green-400');
                } else if (statusLower === 'pending' || statusLower === 'belum bayar') {
                    statusEl.classList.add('bg-yellow-500/20', 'text-yellow-400');
                } else {
                    statusEl.classList.add('bg-red-500/20', 'text-red-400');
                }

                const tbody = document.getElementById('detail-items-body');
                tbody.innerHTML = '';
                const items = sale.sales_items || sale.salesItems || [];
                items.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-gray-800 text-[11px]';
                    tr.innerHTML = `
                            <td class="p-2 text-gray-400 font-semibold">${index + 1}</td>
                            <td class="p-2 font-bold text-white">${item.product_name}</td>
                            <td class="p-2 text-gray-300 font-mono">${item.sku}</td>
                            <td class="p-2 text-center text-white">${item.qty} ${item.unit || 'pcs'}</td>
                            <td class="p-2 text-right text-white">Rp ${Math.round(item.price).toLocaleString('id-ID')}</td>
                            <td class="p-2 text-right font-bold text-[#B4F481]">Rp ${Math.round(item.subtotal).toLocaleString('id-ID')}</td>
                        `;
                    tbody.appendChild(tr);
                });

                document.getElementById('detail-subtotal').textContent = 'Rp ' + Math.round(sale.subtotal).toLocaleString('id-ID');
                document.getElementById('detail-discount').textContent = 'Rp ' + Math.round(sale.discount).toLocaleString('id-ID');
                document.getElementById('detail-tax').textContent = 'Rp ' + Math.round(sale.tax).toLocaleString('id-ID');
                document.getElementById('detail-grand-total').textContent = 'Rp ' + Math.round(sale.grand_total).toLocaleString('id-ID');

                document.getElementById('detail-modal').classList.remove('hidden');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memuat detail penjualan.');
            }
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }
        function formatRupiahNumber(num) {
            return num.toLocaleString('id-ID').replace(/,/g, '.');
        }

        function formatRupiahInput(element) {
            let value = element.value.replace(/[^0-9]/g, '');
            if (value) {
                element.value = parseInt(value, 10).toLocaleString('id-ID').replace(/,/g, '.');
            } else {
                element.value = '';
            }
        }

        function updateHiddenVal(displayEl, hiddenId) {
            const val = displayEl.value.replace(/\./g, '');
            document.getElementById(hiddenId).value = parseFloat(val) || 0;
            recalcCreate();
        }

        function parseRupiahNumber(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        function updateWholesalePricesSelect(prefix) {
            const select = document.getElementById(`${prefix}-item-product`);
            const selectedOption = select.options[select.selectedIndex];
            const useWholesaleCheckbox = document.getElementById(`${prefix}-use-wholesale`);
            const selectContainer = document.getElementById(`${prefix}-wholesale-select-container`);
            const wholesaleSelect = document.getElementById(`${prefix}-wholesale-price-select`);

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
                        opt.textContent = `Min. ${wp.min_qty} - Rp ${formatRupiahNumber(wp.price)}`;
                        wholesaleSelect.appendChild(opt);
                    });
                } else {
                    selectContainer.classList.add('hidden');
                }
            } else {
                selectContainer.classList.add('hidden');
            }
        }

        function updatePriceBasedOnQty(prefix) {
            const select = document.getElementById(`${prefix}-item-product`);
            const selectedOption = select.options[select.selectedIndex];
            const qtyInput = document.getElementById(`${prefix}-item-qty`);
            const priceInput = document.getElementById(`${prefix}-item-price`);
            const infoDiv = document.getElementById(`${prefix}-wholesale-info`);
            const useWholesaleCheckbox = document.getElementById(`${prefix}-use-wholesale`);
            const wholesaleSelect = document.getElementById(`${prefix}-wholesale-price-select`);

            if (selectedOption && selectedOption.value) {
                const defaultPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
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

                priceInput.value = formatRupiahNumber(appliedPrice);

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
                        return `<span class="${style}">Min. ${wp.min_qty} = Rp ${formatRupiahNumber(wp.price)}</span>`;
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

        function updateProductPrice(prefix) {
            updateWholesalePricesSelect(prefix);
            updatePriceBasedOnQty(prefix);
        }

        function renameFormInputs(prefix) {
            const rows = document.querySelectorAll(`#${prefix}-items-body tr`);
            rows.forEach((row, index) => {
                const prodInput = row.querySelector('input[data-field="product_id"]');
                const qtyInput = row.querySelector('input[data-field="qty"]');
                const priceInput = row.querySelector('input[data-field="price"]');
                if (prodInput) prodInput.name = `items[${index}][product_id]`;
                if (qtyInput) qtyInput.name = `items[${index}][qty]`;
                if (priceInput) priceInput.name = `items[${index}][price]`;
            });
        }

        function addItem(prefix) {
            const productSelect = document.getElementById(`${prefix}-item-product`);
            const qtyInput = document.getElementById(`${prefix}-item-qty`);
            const priceInput = document.getElementById(`${prefix}-item-price`);
            const itemsBody = document.getElementById(`${prefix}-items-body`);
            const noItemsRow = document.getElementById(`${prefix}-no-items`);
            const productId = productSelect.value;
            if (!productId) return;
            const productText = productSelect.options[productSelect.selectedIndex].text;
            const price = parseRupiahNumber(priceInput.value);
            const qty = parseInt(qtyInput.value) || 1;
            const subtotal = price * qty;
            if (noItemsRow) noItemsRow.remove();
            const row = document.createElement('tr');
            row.innerHTML = `
                    <td class="p-2">
                        ${productText}
                        <input type="hidden" data-field="product_id" value="${productId}">
                        <input type="hidden" data-field="qty" value="${qty}">
                        <input type="hidden" data-field="price" value="${price}">
                    </td>
                    <td class="p-2 text-center">${qty}</td>
                    <td class="p-2 text-right">${formatRupiahNumber(price)}</td>
                    <td class="p-2 text-right" data-raw-subtotal="${subtotal}">${formatRupiahNumber(subtotal)}</td>
                    <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove(); renameFormInputs('${prefix}'); recalc${prefix.charAt(0).toUpperCase() + prefix.slice(1)}();">✕</button></td>
                `;
            itemsBody.appendChild(row);
            renameFormInputs(prefix);
            recalcCreate();
            productSelect.value = '';
            qtyInput.value = '';
            priceInput.value = '';

            // Reset wholesale options
            const useWholesaleCheckbox = document.getElementById(`${prefix}-use-wholesale`);
            if (useWholesaleCheckbox) useWholesaleCheckbox.checked = false;
            updateWholesalePricesSelect(prefix);
            updatePriceBasedOnQty(prefix);
        }

        function recalcCreate() {
            const rows = document.querySelectorAll('#create-items-body tr');
            let subtotal = 0;
            rows.forEach(row => {
                const subEl = row.querySelector('td[data-raw-subtotal]');
                if (subEl) {
                    const sub = parseFloat(subEl.dataset.rawSubtotal) || 0;
                    subtotal += sub;
                }
            });
            const discount = parseFloat(document.getElementById('create-discount').value) || 0;
            const tax = parseFloat(document.getElementById('create-tax').value) || 0;
            const total = subtotal - discount + tax;
            document.getElementById('create-subtotal').value = subtotal;
            document.getElementById('create-subtotal-display').value = formatRupiahNumber(subtotal);
            document.getElementById('create-grand_total').value = total;
            document.getElementById('create-grand_total-display').value = formatRupiahNumber(total);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initial formatting on load
            const discountDisp = document.getElementById('create-discount-display');
            if (discountDisp && discountDisp.value) {
                formatRupiahInput(discountDisp);
            }
            const taxDisp = document.getElementById('create-tax-display');
            if (taxDisp && taxDisp.value) {
                formatRupiahInput(taxDisp);
            }
            const subtotalDisp = document.getElementById('create-subtotal-display');
            const subtotalVal = parseFloat(document.getElementById('create-subtotal').value) || 0;
            if (subtotalDisp) {
                subtotalDisp.value = formatRupiahNumber(subtotalVal);
            }
            const grandTotalDisp = document.getElementById('create-grand_total-display');
            const grandTotalVal = parseFloat(document.getElementById('create-grand_total').value) || 0;
            if (grandTotalDisp) {
                grandTotalDisp.value = formatRupiahNumber(grandTotalVal);
            }

            // Add wholesale event listeners for create form
            const prodSelect = document.getElementById('create-item-product');
            const qtyIn = document.getElementById('create-item-qty');
            const useWholesaleCheckbox = document.getElementById('create-use-wholesale');
            const wholesaleSelect = document.getElementById('create-wholesale-price-select');

            if (prodSelect) {
                prodSelect.addEventListener('change', function() {
                    updateProductPrice('create');
                });
            }
            if (qtyIn) {
                qtyIn.addEventListener('input', function() {
                    updatePriceBasedOnQty('create');
                });
            }
            if (useWholesaleCheckbox) {
                useWholesaleCheckbox.addEventListener('change', function() {
                    updateWholesalePricesSelect('create');
                    updatePriceBasedOnQty('create');
                });
            }
            if (wholesaleSelect) {
                wholesaleSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const qtyField = document.getElementById('create-item-qty');
                    if (selectedOption && selectedOption.value) {
                        qtyField.value = parseInt(selectedOption.value);
                    }
                    updatePriceBasedOnQty('create');
                });
            }
        });
        function updateStatusFromPaymentMethod(prefix) {
            const method = document.getElementById(`${prefix}-payment_method`).value;
            const statusField = document.getElementById(`${prefix}-status`);
            statusField.value = (method === 'TUNAI' || method === 'TRANSFER') ? 'LUNAS' : 'BELUM BAYAR';
        }
    </script>
@endsection