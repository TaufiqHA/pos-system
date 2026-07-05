@extends('layouts.cabang')

@section('title', 'Daftar PO - POS')
@section('page_title', 'DAFTAR PURCHASE ORDER')
@section('page_subtitle', 'Kelola pengajuan Purchase Order (PO) ke pusat')

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
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2.5 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition cursor-pointer">
            PO KE PUSAT
        </button>
    </div>

    <!-- Tabel Riwayat Purchase Order -->
    <div class="card p-6 rounded-2xl shadow-xl z-10 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">RIWAYAT PURCHASE ORDER</h3>
            </div>
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
                            <td class="py-4 px-4 font-semibold text-white">{{ $po->po_number }}</td>
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
                            <td colspan="7" class="py-8 text-center text-gray-500">Belum ada Purchase Order terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL BOX: PO KE PUSAT ================= -->
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
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <select id="po-product-select"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-sku="{{ $product->sku }}"
                                        data-price="{{ $product->buy_price }}" data-name="{{ $product->name }}">
                                        {{ $product->name }} (SKU: {{ $product->sku }}) - Rp
                                        {{ number_format($product->buy_price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-24">
                            <input type="number" id="po-qty-input" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
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
                </div>

                <!-- Table Item PO -->
                <div class="bg-gray-900/40 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                                <th class="py-3 px-4 font-semibold">Produk</th>
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

    <!-- ================= MODAL BOX: DETAIL PO ================= -->
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
                <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Purchase Order</h3>
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
        }

        function formatCurrency(value) {
            return Math.round(value).toLocaleString('id-ID');
        }

        function parseCurrency(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        // Auto prefill price when product changes
        document.getElementById('po-product-select').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const priceInput = document.getElementById('po-price-input');
            if (selectedOption && selectedOption.value) {
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                priceInput.value = formatCurrency(price);
            } else {
                priceInput.value = '';
            }
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

            const productId = selectedOption.value;
            const name = selectedOption.getAttribute('data-name');
            const sku = selectedOption.getAttribute('data-sku');

            // Check if product already added
            const existingIndex = poItems.findIndex(item => item.product_id === productId);
            if (existingIndex > -1) {
                poItems[existingIndex].qty += qty;
            } else {
                poItems.push({
                    product_id: productId,
                    name: name,
                    sku: sku,
                    qty: qty,
                    price: price
                });
            }

            // Reset inputs
            select.value = '';
            qtyInput.value = '';
            priceInput.value = '';

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
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-gray-500 font-medium">Belum ada item ditambahkan</td></tr>`;
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
                tr.innerHTML = `
                    <td class="py-3 px-4">
                        <div class="font-bold text-white">${item.name}</div>
                        <div class="text-[10px] text-gray-400">SKU: ${item.sku}</div>
                    </td>
                    <td class="py-3 px-4 text-white">${item.qty}</td>
                    <td class="py-3 px-4 text-white">Rp ${item.price.toLocaleString('id-ID')}</td>
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
