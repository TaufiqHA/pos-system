@extends('layouts.cabang')

@section('title', 'Penjualan Cabang - POS')
@section('page_title', 'PENJUALAN CABANG')
@section('page_subtitle', 'Riwayat Penjualan Cabang')

@section('content')
    @if(session('success'))
        <div id="success-alert" class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-6 rounded-2xl shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <div></div>
            <div class="flex items-center gap-3">
                    <button onclick="window.location='{{ route('purchase-orders.index') }}'" class="w-full sm:w-auto justify-center border border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Daftar PO
                    </button>
                    <button onclick="openCreateModal()"
                        class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
                        <th class="pb-3 px-4">Kasir</th>
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
                            <td class="py-4 px-4">{{ $sale->user->name ?? '—' }}</td>
                            <td class="py-4 px-4 text-right font-semibold">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-2 py-1 text-xs rounded-full {{ $sale->status == 'completed' ? 'bg-green-600/30 text-green-300' : ($sale->status == 'pending' ? 'bg-yellow-600/30 text-yellow-300' : 'bg-red-600/30 text-red-300') }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td class="py-4 pl-4 pr-4 text-center">
                                <a href="{{ route('sales.show', $sale->id) }}" class="text-indigo-400 hover:text-indigo-200 transition-colors text-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">Tidak ada data penjualan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL BOX: TAMBAH PENJUALAN ================= -->
    <div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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
                        <label for="create-date" class="block font-bold text-gray-300">Tanggal Transaksi <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="date" id="create-date" value="{{ old('date') ?? now()->format('Y-m-d\TH:i') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        @error('date')<p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3">
                    <div class="sm:col-span-2">
                        <select id="create-item-product" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sell_price ?? $product->buy_price }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="number" id="create-item-qty" placeholder="Qty" min="1" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="create-item-price" placeholder="Harga" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-2.5 focus:outline-none cursor-not-allowed" readonly>
                        <button type="button" onclick="addItem('create')" class="bg-[#B4F481] hover:bg-green-400 text-black px-3.5 rounded-xl font-bold cursor-pointer">+</button>
                    </div>
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
                            <tr id="create-no-items"><td colspan="5" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="create-subtotal-display" class="block font-bold text-gray-300">Subtotal <span class="text-red-500">*</span></label>
                        <input type="text" id="create-subtotal-display" value="{{ old('subtotal') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                        <input type="hidden" name="subtotal" id="create-subtotal" value="{{ old('subtotal') ?? 0 }}">
                    </div>
                    <div class="space-y-1">
                        <label for="create-discount" class="block font-bold text-gray-300">Diskon</label>
                        <input type="number" name="discount" id="create-discount" value="{{ old('discount') ?? 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" min="0">
                    </div>
                    <div class="space-y-1">
                        <label for="create-tax" class="block font-bold text-gray-300">Pajak</label>
                        <input type="number" name="tax" id="create-tax" value="{{ old('tax') ?? 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" min="0">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="create-grand_total-display" class="block font-bold text-gray-300">Total <span class="text-red-5">*</span></label>
                        <input type="text" id="create-grand_total-display" value="{{ old('grand_total') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                        <input type="hidden" name="grand_total" id="create-grand_total" value="{{ old('grand_total') ?? 0 }}">
                    </div>
                    <div class="space-y-1">
                        <label for="create-payment_method" class="block font-bold text-gray-300">Metode Pembayaran <span class="text-red-500">*</span></label>
                        <select name="payment_method" id="create-payment_method" onchange="updateStatusFromPaymentMethod('create')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="KREDIT">KREDIT</option>
                        </select>
                        <input type="hidden" name="status" id="create-status" value="LUNAS">
                    </div>
                </div>
                <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">Batal</button>
                    <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">Simpan Penjualan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() { document.getElementById('create-modal').classList.remove('hidden'); }
        function closeCreateModal() { document.getElementById('create-modal').classList.add('hidden'); }
        function addItem(prefix) {
            const productSelect = document.getElementById(`${prefix}-item-product`);
            const qtyInput = document.getElementById(`${prefix}-item-qty`);
            const priceInput = document.getElementById(`${prefix}-item-price`);
            const itemsBody = document.getElementById(`${prefix}-items-body`);
            const noItemsRow = document.getElementById(`${prefix}-no-items`);
            const productId = productSelect.value;
            if (!productId) return;
            const productText = productSelect.options[productSelect.selectedIndex].text;
            const price = productSelect.options[productSelect.selectedIndex].dataset.price || 0;
            const qty = parseInt(qtyInput.value) || 1;
            const subtotal = price * qty;
            priceInput.value = price;
            if (noItemsRow) noItemsRow.remove();
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="p-2">${productText}</td>
                <td class="p-2 text-center">${qty}</td>
                <td class="p-2 text-right">${price}</td>
                <td class="p-2 text-right">${subtotal}</td>
                <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove(); recalc${prefix.charAt(0).toUpperCase()+prefix.slice(1)}();">✕</button></td>
            `;
            itemsBody.appendChild(row);
            recalcCreate();
            productSelect.value = '';
            qtyInput.value = '';
            priceInput.value = '';
        }
        function recalcCreate() {
            const rows = document.querySelectorAll('#create-items-body tr');
            let subtotal = 0;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    const sub = parseFloat(cells[3].textContent) || 0;
                    subtotal += sub;
                }
            });
            const discount = parseFloat(document.getElementById('create-discount').value) || 0;
            const tax = parseFloat(document.getElementById('create-tax').value) || 0;
            const total = subtotal - discount + tax;
            document.getElementById('create-subtotal').value = subtotal;
            document.getElementById('create-subtotal-display').value = subtotal;
            document.getElementById('create-grand_total').value = total;
            document.getElementById('create-grand_total-display').value = total;
        }
        document.getElementById('create-discount').addEventListener('input', recalcCreate);
        document.getElementById('create-tax').addEventListener('input', recalcCreate);
        function updateStatusFromPaymentMethod(prefix) {
            const method = document.getElementById(`${prefix}-payment_method`).value;
            const statusField = document.getElementById(`${prefix}-status`);
            statusField.value = (method === 'TUNAI' || method === 'TRANSFER') ? 'LUNAS' : 'BELUM BAYAR';
        }
    </script>
@endsection