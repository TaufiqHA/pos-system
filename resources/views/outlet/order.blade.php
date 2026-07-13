@extends('layouts.outlet')

@section('title', 'Order Outlet - POS')
@section('page_title', 'DAFTAR ORDER BARANG')
@section('page_subtitle', 'Kelola pengajuan Purchase Order (PO) ke Cabang')

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

    <!-- Tabel Riwayat Purchase Order -->
    <div class="card p-6 rounded-2xl shadow-xl z-10 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">RIWAYAT ORDER OUTLET</h3>
            </div>
            <button onclick="openPoModal()"
                class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                BUAT ORDER
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                        <th class="pb-3 pl-4 pr-4">No</th>
                        <th class="pb-3 px-4">No PO</th>
                        <th class="pb-3 px-4">Tanggal</th>
                        <th class="pb-3 px-4">Pembuat</th>
                        <th class="pb-3 px-4">Status</th>
                        <th class="pb-3 px-4">Grand Total</th>
                        <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                    @forelse($purchaseOrders as $po)
                        @php
                            $notes = json_decode($po->notes, true);
                            $grandTotal = $notes['grand_total'] ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                            <td class="py-4 px-4 font-semibold text-white font-mono">{{ $po->po_number }}</td>
                            <td class="py-4 px-4 text-gray-300">{{ $po->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-4 px-4 text-gray-300">{{ $po->user->name ?? 'User' }}</td>
                            <td class="py-4 px-4">
                                @if($po->status === 'Pending')
                                    <span class="bg-yellow-500/10 text-yellow-500 px-2.5 py-1 rounded-full text-[10px] font-bold border border-yellow-500/20">
                                        Pending
                                    </span>
                                @elseif($po->status === 'Approved')
                                    <span class="bg-blue-500/10 text-blue-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-blue-500/20">
                                        Approved
                                    </span>
                                @elseif($po->status === 'Completed')
                                    <span class="bg-green-500/10 text-green-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-green-500/20">
                                        Completed
                                    </span>
                                @elseif($po->status === 'Rejected')
                                    <span class="bg-red-500/10 text-red-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-red-500/20">
                                        Rejected
                                    </span>
                                @else
                                    <span class="bg-gray-500/10 text-gray-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-gray-500/20">
                                        {{ $po->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 font-bold text-[#B4F481]">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                                <div class="flex justify-end items-center gap-2">
                                    <button onclick="openDetailModal({{ json_encode($po) }}, {{ json_encode($notes) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">Belum ada Order terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL BOX: BUAT ORDER ================= -->
    <div id="po-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-4xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
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
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Item Order -->
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

                <!-- Table Item Order -->
                <div class="bg-gray-900/40 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
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

                <!-- Ringkasan Total (Subtotal & Grand Total) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Subtotal *</label>
                        <input type="text" id="po-subtotal-display" value="0" readonly
                            class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        <input type="hidden" id="po-subtotal" name="subtotal" value="0">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Grand Total *</label>
                        <input type="text" id="po-grand-total-display" value="0" readonly
                            class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        <input type="hidden" id="po-grand-total" name="grand_total" value="0">
                    </div>
                    <!-- Hidden inputs for discount and tax to prevent breaking JS / controller logic -->
                    <input type="hidden" id="po-discount-input" name="discount" value="0">
                    <input type="hidden" id="po-tax-input" name="tax" value="0">
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
                <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-850">
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

    <!-- ================= MODAL BOX: DETAIL ORDER ================= -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Order Barang</h3>
                <p class="text-[11px] text-gray-400 mt-1" id="detail-po-number">PO Number</p>
            </div>

            <!-- Detail Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-xs text-gray-300">
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500 font-bold block">TANGGAL</span>
                        <span id="detail-date" class="text-white font-semibold"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold block">PEMBUAT</span>
                        <span id="detail-creator" class="text-white font-semibold"></span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-500 font-bold block">STATUS</span>
                        <span id="detail-status" class="inline-block"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold block">CATATAN</span>
                        <span id="detail-notes" class="text-white font-medium italic"></span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-3">Item Detail</h4>
            <div class="bg-gray-900/40 rounded-xl overflow-hidden mb-6">
                <table class="w-full text-left text-gray-300 border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                            <th class="py-3 px-4 font-semibold">Produk</th>
                            <th class="py-3 px-4 font-semibold text-center">Qty</th>
                            <th class="py-3 px-4 font-semibold text-right">Harga</th>
                            <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail-items-body">
                        <!-- Filled dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="border-t border-gray-800 pt-4 space-y-2 text-xs text-gray-300">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="detail-subtotal" class="font-bold text-white"></span>
                </div>
                <div class="flex justify-between">
                    <span>Diskon</span>
                    <span id="detail-discount" class="font-bold text-white"></span>
                </div>
                <div class="flex justify-between">
                    <span>Pajak</span>
                    <span id="detail-tax" class="font-bold text-white"></span>
                </div>
                <div class="flex justify-between border-t border-gray-800 pt-2 text-sm">
                    <span class="font-bold text-white">Grand Total</span>
                    <span id="detail-grandtotal" class="font-extrabold text-[#B4F481]"></span>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="closeDetailModal()" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition cursor-pointer text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        // --- JS UNTUK MODAL CREATE PO ---
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
                    image: image,
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

        // Keep table synchronization and calculations
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

        // --- JS UNTUK MODAL DETAIL PO ---
        function openDetailModal(po, notes) {
            document.getElementById('detail-po-number').innerText = po.po_number;
            document.getElementById('detail-date').innerText = new Date(po.created_at).toLocaleString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            document.getElementById('detail-creator').innerText = po.user ? po.user.name : '-';
            
            // Status styling
            const statusEl = document.getElementById('detail-status');
            statusEl.innerText = po.status;
            statusEl.className = 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border ';
            if (po.status === 'Pending') {
                statusEl.className += 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
            } else if (po.status === 'Approved') {
                statusEl.className += 'bg-blue-500/10 text-blue-400 border-blue-500/20';
            } else if (po.status === 'Completed') {
                statusEl.className += 'bg-green-500/10 text-green-400 border-green-500/20';
            } else if (po.status === 'Rejected') {
                statusEl.className += 'bg-red-500/10 text-red-400 border-red-500/20';
            } else {
                statusEl.className += 'bg-gray-500/10 text-gray-400 border-gray-500/20';
            }

            document.getElementById('detail-notes').innerText = notes.user_notes || '-';
            document.getElementById('detail-subtotal').innerText = 'Rp ' + formatCurrency(notes.subtotal || 0);
            document.getElementById('detail-discount').innerText = 'Rp ' + formatCurrency(notes.discount || 0);
            document.getElementById('detail-tax').innerText = 'Rp ' + formatCurrency(notes.tax || 0);
            document.getElementById('detail-grandtotal').innerText = 'Rp ' + formatCurrency(notes.grand_total || 0);

            // Populate table body
            const tbody = document.getElementById('detail-items-body');
            tbody.innerHTML = '';
            
            const items = notes.items || [];
            if (items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-gray-500">Tidak ada item detail</td></tr>`;
            } else {
                items.forEach(item => {
                    const price = parseFloat(item.price) || 0;
                    const qty = parseInt(item.qty) || 0;
                    const subtotal = price * qty;
                    
                    tbody.innerHTML += `
                        <tr class="border-b border-gray-800 hover:bg-gray-800/20 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">${item.name}</div>
                                <div class="text-[10px] text-gray-500">SKU: ${item.sku}</div>
                            </td>
                            <td class="py-3 px-4 text-center text-white">${qty}</td>
                            <td class="py-3 px-4 text-right text-white">Rp ${formatCurrency(price)}</td>
                            <td class="py-3 px-4 text-right text-white font-semibold">Rp ${formatCurrency(subtotal)}</td>
                        </tr>
                    `;
                });
            }

            document.getElementById('detail-modal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }

        // Auto-open PO modal if action=create is passed in query string
        @if(request('action') === 'create')
            document.addEventListener('DOMContentLoaded', function() {
                openPoModal();
            });
        @endif
    </script>
@endsection
