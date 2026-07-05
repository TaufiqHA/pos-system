@extends('layouts.admin')

@section('title', 'Daftar Penjualan - Lucifer POS')

@section('page_title', 'Daftar Penjualan')
@section('page_subtitle', 'Mengelola transaksi penjualan barang')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex justify-between items-center mb-6">
        <div></div>
        <div class="flex items-center gap-3">
            <button class="w-full sm:w-auto justify-center border border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Daftar PO
            </button>
            <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Penjualan
            </button>
        </div>
    </div>

    @if (session('success'))
        <div id="success-alert" class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4 pr-4">No</th>
                    <th class="pb-3 px-4">Invoice</th>
                    <th class="pb-3 px-4">Tanggal</th>
                    <th class="pb-3 px-4">Cabang</th>
                    <th class="pb-3 px-4">Total</th>
                    <th class="pb-3 px-4">Metode</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($sales as $sale)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white font-mono">{{ $sale->invoice }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y H:i') }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $sale->branch?->name ?? '-' }}</td>
                        <td class="py-4 px-4 text-white font-semibold">Rp {{ number_format($sale->grand_total, 2, ',', '.') }}</td>
                        <td class="py-4 px-4 text-gray-400 font-semibold">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-400 uppercase">
                                {{ $sale->salesPayments->first()?->method ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold
                                @if($sale->status === 'completed' || $sale->status === 'LUNAS') bg-green-500/20 text-green-400
                                @elseif($sale->status === 'pending' || $sale->status === 'BELUM BAYAR') bg-yellow-500/20 text-yellow-400
                                @else bg-red-500/20 text-red-400 @endif">
                                {{ strtoupper($sale->status) }}
                            </span>
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($sale) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($sale) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">Belum ada transaksi penjualan terdaftar.</td>
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
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Penjualan Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat data transaksi penjualan baru</p>
        </div>
        <form action="{{ route('sales.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <input type="hidden" name="user_id" id="create-user_id" value="{{ Auth::user()->id }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-branch_id" class="block font-bold text-gray-300">Cabang Tujuan <span class="text-red-500">*</span></label>
                    <select name="branch_id" id="create-branch_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="create-date" class="block font-bold text-gray-300">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="date" id="create-date" value="{{ old('date') ?? now()->format('Y-m-d\TH:i') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                    @error('date')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-800 pt-4 mt-4">
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3">
                    <div class="sm:col-span-2">
                        <select id="create-item-product" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
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
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label for="create-subtotal-display" class="block font-bold text-gray-300">Subtotal <span class="text-red-500">*</span></label>
                    <input type="text" id="create-subtotal-display" value="{{ old('subtotal') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                    <input type="hidden" name="subtotal" id="create-subtotal" value="{{ old('subtotal') ?? 0 }}">
                    @error('subtotal')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="create-discount-display" class="block font-bold text-gray-300">Diskon</label>
                    <input type="text" id="create-discount-display" value="{{ old('discount') ?? '0' }}" oninput="formatInputWithDots(this, 'create-discount', 'create-')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                    <input type="hidden" name="discount" id="create-discount" value="{{ old('discount') ?? 0 }}">
                    @error('discount')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="create-tax-display" class="block font-bold text-gray-300">Pajak</label>
                    <input type="text" id="create-tax-display" value="{{ old('tax') ?? '0' }}" oninput="formatInputWithDots(this, 'create-tax', 'create-')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                    <input type="hidden" name="tax" id="create-tax" value="{{ old('tax') ?? 0 }}">
                    @error('tax')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-grand_total-display" class="block font-bold text-gray-300">Grand Total <span class="text-red-500">*</span></label>
                    <input type="text" id="create-grand_total-display" value="{{ old('grand_total') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                    <input type="hidden" name="grand_total" id="create-grand_total" value="{{ old('grand_total') ?? 0 }}">
                    @error('grand_total')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="create-payment_method" class="block font-bold text-gray-300">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" id="create-payment_method" onchange="updateStatusFromPaymentMethod('create')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        <option value="TUNAI">TUNAI</option>
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="KREDIT">KREDIT</option>
                    </select>
                    <input type="hidden" name="status" id="create-status" value="LUNAS">
                    @error('status')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeCreateModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan Penjualan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT PENJUALAN ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Penjualan</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data transaksi penjualan</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('sales.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <input type="hidden" name="user_id" id="edit-user_id" value="{{ old('user_id') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-branch_id" class="block font-bold text-gray-300">Cabang Tujuan <span class="text-red-500">*</span></label>
                    <select name="branch_id" id="edit-branch_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="edit-date" class="block font-bold text-gray-300">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="date" id="edit-date" value="{{ old('date') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                    @error('date')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-gray-800 pt-4 mt-4">
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3">
                    <div class="sm:col-span-2">
                        <select id="edit-item-product" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="number" id="edit-item-qty" placeholder="Qty" min="1" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="edit-item-price" placeholder="Harga" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-2.5 focus:outline-none cursor-not-allowed" readonly>
                        <button type="button" onclick="addItem('edit')" class="bg-[#B4F481] hover:bg-green-400 text-black px-3.5 rounded-xl font-bold cursor-pointer">+</button>
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
                        <tbody id="edit-items-body" class="divide-y divide-gray-800 text-gray-300">
                            <tr id="edit-no-items"><td colspan="5" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label for="edit-subtotal-display" class="block font-bold text-gray-300">Subtotal <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-subtotal-display" value="{{ old('subtotal') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                    <input type="hidden" name="subtotal" id="edit-subtotal" value="{{ old('subtotal') ?? 0 }}">
                    @error('subtotal')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="edit-discount-display" class="block font-bold text-gray-300">Diskon</label>
                    <input type="text" id="edit-discount-display" value="{{ old('discount') ?? '0' }}" oninput="formatInputWithDots(this, 'edit-discount', 'edit-')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                    <input type="hidden" name="discount" id="edit-discount" value="{{ old('discount') ?? 0 }}">
                    @error('discount')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="edit-tax-display" class="block font-bold text-gray-300">Pajak</label>
                    <input type="text" id="edit-tax-display" value="{{ old('tax') ?? '0' }}" oninput="formatInputWithDots(this, 'edit-tax', 'edit-')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                    <input type="hidden" name="tax" id="edit-tax" value="{{ old('tax') ?? 0 }}">
                    @error('tax')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-grand_total-display" class="block font-bold text-gray-300">Grand Total <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-grand_total-display" value="{{ old('grand_total') ?? '0' }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed" readonly required>
                    <input type="hidden" name="grand_total" id="edit-grand_total" value="{{ old('grand_total') ?? 0 }}">
                    @error('grand_total')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="edit-payment_method" class="block font-bold text-gray-300">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" id="edit-payment_method" onchange="updateStatusFromPaymentMethod('edit')" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        <option value="TUNAI">TUNAI</option>
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="KREDIT">KREDIT</option>
                    </select>
                    <input type="hidden" name="status" id="edit-status" value="LUNAS">
                    @error('status')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: DETAIL PENJUALAN ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Penjualan</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap transaksi penjualan</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Penjualan</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-[10px] select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">No. Invoice</p>
                    <p id="detail-invoice" class="text-white font-mono font-bold mt-0.5 text-xs select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Cabang</p>
                    <p id="detail-branch" class="text-white mt-0.5 font-semibold"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Operator / User</p>
                    <p id="detail-user" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Tanggal</p>
                    <p id="detail-date" class="text-white mt-0.5"></p>
                </div>
            </div>

            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Subtotal</p>
                    <p id="detail-subtotal" class="text-white mt-0.5 font-mono"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Diskon</p>
                    <p id="detail-discount" class="text-white mt-0.5 font-mono"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Pajak</p>
                    <p id="detail-tax" class="text-white mt-0.5 font-mono"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Grand Total</p>
                    <p id="detail-grand_total" class="text-white mt-0.5 font-mono font-bold text-sm text-[#B4F481]"></p>
                </div>
            </div>

            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Status Transaksi</p>
                    <span id="detail-status" class="inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase"></span>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Terdaftar Pada</p>
                    <p id="detail-created" class="text-white mt-0.5"></p>
                </div>
            </div>

            <div class="bg-gray-900/50 p-4 rounded-xl">
                <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px] mb-2">Item Penjualan</p>
                <div class="overflow-x-auto max-h-[200px] overflow-y-auto border border-gray-800 rounded-xl">
                    <table class="w-full text-left border-collapse text-[10px]">
                        <thead>
                            <tr class="bg-gray-950 text-gray-400 font-bold border-b border-gray-800">
                                <th class="p-2">Produk (SKU)</th>
                                <th class="p-2 text-center">Qty / Satuan</th>
                                <th class="p-2 text-right">Harga</th>
                                <th class="p-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-body" class="divide-y divide-gray-800 text-gray-300">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end">
                <button onclick="closeDetailModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const availableProducts = @json($products);
    let createItems = [];
    let editItems = [];

    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(() => {
                    successAlert.remove();
                }, 500);
            }, 3000);
        }

        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const id = urlParams.get('id');

        if (action === 'create') {
            openCreateModal();
        } else if (action === 'edit' && id) {
            const salesData = @json($sales);
            const sale = salesData.find(s => s.id === id);
            if (sale) {
                openEditModal(sale);
            }
        }

        document.getElementById('create-item-product').addEventListener('change', function() {
            const productId = this.value;
            const priceInput = document.getElementById('create-item-price');
            const qtyInput = document.getElementById('create-item-qty');

            const product = availableProducts.find(p => p.id === productId);
            if (product) {
                priceInput.value = formatNumberWithDots(product.sell_price);
                if (!qtyInput.value) qtyInput.value = 1;
            } else {
                priceInput.value = '';
            }
        });

        document.getElementById('edit-item-product').addEventListener('change', function() {
            const productId = this.value;
            const priceInput = document.getElementById('edit-item-price');
            const qtyInput = document.getElementById('edit-item-qty');

            const product = availableProducts.find(p => p.id === productId);
            if (product) {
                priceInput.value = formatNumberWithDots(product.sell_price);
                if (!qtyInput.value) qtyInput.value = 1;
            } else {
                priceInput.value = '';
            }
        });
    });

    function formatNumberWithDots(val) {
        const num = parseFloat(val) || 0;
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
    }

    function formatInputWithDots(input, hiddenId, prefix) {
        let cleanVal = input.value.replace(/\D/g, '');
        if (!cleanVal) cleanVal = '0';
        const num = parseFloat(cleanVal);
        document.getElementById(hiddenId).value = num;
        input.value = formatNumberWithDots(num);
        calculateGrandTotal(prefix);
    }

    function updateStatusFromPaymentMethod(prefix) {
        const method = document.getElementById(`${prefix}-payment_method`).value;
        const statusInput = document.getElementById(`${prefix}-status`);
        if (method === 'TUNAI' || method === 'TRANSFER') {
            statusInput.value = 'LUNAS';
        } else if (method === 'KREDIT') {
            statusInput.value = 'BELUM BAYAR';
        }
    }

    function calculateGrandTotal(prefix) {
        const subtotalInput = document.getElementById(prefix + 'subtotal');
        const subtotalDisplayInput = document.getElementById(prefix + 'subtotal-display');
        const discountInput = document.getElementById(prefix + 'discount');
        const taxInput = document.getElementById(prefix + 'tax');
        const grandTotalInput = document.getElementById(prefix + 'grand_total');
        const grandTotalDisplayInput = document.getElementById(prefix + 'grand_total-display');

        const subtotal = parseFloat(subtotalInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;

        const grandTotal = subtotal - discount + tax;
        const finalGrandTotal = grandTotal >= 0 ? grandTotal : 0;

        grandTotalInput.value = finalGrandTotal.toFixed(2);
        grandTotalDisplayInput.value = formatNumberWithDots(finalGrandTotal);
    }

    function addItem(prefix) {
        const productSelect = document.getElementById(`${prefix}-item-product`);
        const qtyInput = document.getElementById(`${prefix}-item-qty`);

        const productId = productSelect.value;
        const qty = parseInt(qtyInput.value) || 0;

        if (!productId) {
            alert('Silakan pilih produk terlebih dahulu.');
            return;
        }
        if (qty <= 0) {
            alert('Quantity harus minimal 1.');
            return;
        }

        const product = availableProducts.find(p => p.id === productId);
        if (!product) return;

        const price = parseFloat(product.sell_price) || 0;
        if (price < 0) {
            alert('Harga tidak boleh kurang dari 0.');
            return;
        }

        const itemsArray = prefix === 'create' ? createItems : editItems;

        const existingItem = itemsArray.find(item => item.product_id === productId);
        if (existingItem) {
            existingItem.qty += qty;
            existingItem.price = price;
        } else {
            itemsArray.push({
                product_id: productId,
                product_name: product.name,
                sku: product.sku,
                qty: qty,
                price: price
            });
        }

        productSelect.value = '';
        qtyInput.value = '';
        document.getElementById(`${prefix}-item-price`).value = '';

        renderItems(prefix);
    }

    function removeItem(prefix, index) {
        if (prefix === 'create') {
            createItems.splice(index, 1);
        } else {
            editItems.splice(index, 1);
        }
        renderItems(prefix);
    }

    function renderItems(prefix) {
        const itemsArray = prefix === 'create' ? createItems : editItems;
        const tbody = document.getElementById(`${prefix}-items-body`);
        tbody.innerHTML = '';

        if (itemsArray.length === 0) {
            tbody.innerHTML = `<tr id="${prefix}-no-items"><td colspan="5" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td></tr>`;
            document.getElementById(`${prefix}-subtotal`).value = 0;
            document.getElementById(`${prefix}-subtotal-display`).value = '0';
            calculateGrandTotal(`${prefix}-`);
            return;
        }

        let subtotal = 0;
        itemsArray.forEach((item, index) => {
            const itemSubtotal = item.qty * item.price;
            subtotal += itemSubtotal;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-800/20';
            tr.innerHTML = `
                <td class="p-2">
                    <div class="font-semibold text-white">${item.product_name}</div>
                    <div class="text-[9px] text-gray-500 font-mono">${item.sku}</div>
                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                </td>
                <td class="p-2 text-center text-gray-400 font-semibold">${item.qty}</td>
                <td class="p-2 text-right text-gray-400 font-mono">Rp ${item.price.toLocaleString('id-ID')}</td>
                <td class="p-2 text-right text-white font-semibold font-mono">Rp ${itemSubtotal.toLocaleString('id-ID')}</td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeItem('${prefix}', ${index})" class="text-red-500 hover:text-red-400 transition cursor-pointer">Hapus</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById(`${prefix}-subtotal`).value = subtotal.toFixed(2);
        document.getElementById(`${prefix}-subtotal-display`).value = formatNumberWithDots(subtotal);
        calculateGrandTotal(`${prefix}-`);
    }

    function openCreateModal() {
        createItems = [];
        renderItems('create');
        document.getElementById('create-discount').value = '0';
        document.getElementById('create-discount-display').value = '0';
        document.getElementById('create-tax').value = '0';
        document.getElementById('create-tax-display').value = '0';
        document.getElementById('create-subtotal-display').value = '0';
        document.getElementById('create-grand_total-display').value = '0';

        document.getElementById('create-payment_method').value = 'TUNAI';
        document.getElementById('create-status').value = 'LUNAS';

        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openEditModal(sale) {
        document.getElementById('edit-id').value = sale.id;
        document.getElementById('edit-branch_id').value = sale.branch_id;
        document.getElementById('edit-user_id').value = sale.user_id;

        if (sale.date) {
            const dateObj = new Date(sale.date);
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            document.getElementById('edit-date').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        } else {
            document.getElementById('edit-date').value = '';
        }

        document.getElementById('edit-subtotal').value = sale.subtotal;
        document.getElementById('edit-subtotal-display').value = formatNumberWithDots(sale.subtotal);
        document.getElementById('edit-discount').value = sale.discount;
        document.getElementById('edit-discount-display').value = formatNumberWithDots(sale.discount);
        document.getElementById('edit-tax').value = sale.tax;
        document.getElementById('edit-tax-display').value = formatNumberWithDots(sale.tax);
        document.getElementById('edit-grand_total').value = sale.grand_total;
        document.getElementById('edit-grand_total-display').value = formatNumberWithDots(sale.grand_total);
        document.getElementById('edit-status').value = sale.status;

        const methodSelect = document.getElementById('edit-payment_method');
        if (sale.status === 'LUNAS') {
            methodSelect.value = 'TUNAI';
        } else if (sale.status === 'BELUM BAYAR') {
            methodSelect.value = 'KREDIT';
        } else {
            if (sale.status === 'completed') {
                methodSelect.value = 'TUNAI';
                document.getElementById('edit-status').value = 'LUNAS';
            } else if (sale.status === 'pending') {
                methodSelect.value = 'KREDIT';
                document.getElementById('edit-status').value = 'BELUM BAYAR';
            } else {
                methodSelect.value = 'TUNAI';
            }
        }

        editItems = sale.sales_items ? sale.sales_items.map(item => ({
            product_id: item.product_id,
            product_name: item.product_name,
            sku: item.sku,
            qty: item.qty,
            price: parseFloat(item.price)
        })) : [];
        renderItems('edit');

        document.getElementById('edit-form').action = `/admin/sales/${sale.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openDetailModal(sale) {
        document.getElementById('detail-id').textContent = sale.id;
        document.getElementById('detail-invoice').textContent = sale.invoice;
        document.getElementById('detail-branch').textContent = sale.branch ? sale.branch.name : '-';
        document.getElementById('detail-user').textContent = sale.user ? sale.user.name : '-';

        const saleDate = sale.date ? new Date(sale.date).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
        document.getElementById('detail-date').textContent = saleDate;

        const formatRupiah = (val) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(val);
        };

        document.getElementById('detail-subtotal').textContent = formatRupiah(sale.subtotal);
        document.getElementById('detail-discount').textContent = formatRupiah(sale.discount);
        document.getElementById('detail-tax').textContent = formatRupiah(sale.tax);
        document.getElementById('detail-grand_total').textContent = formatRupiah(sale.grand_total);

        const statusEl = document.getElementById('detail-status');
        statusEl.textContent = sale.status;
        statusEl.className = 'inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase ';
        if (sale.status === 'LUNAS' || sale.status === 'completed') {
            statusEl.classList.add('bg-green-500/20', 'text-green-400');
        } else if (sale.status === 'BELUM BAYAR' || sale.status === 'pending') {
            statusEl.classList.add('bg-yellow-500/20', 'text-yellow-400');
        } else {
            statusEl.classList.add('bg-red-500/20', 'text-red-400');
        }

        const createdDate = sale.created_at ? new Date(sale.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('detail-created').textContent = createdDate;

        const detailTbody = document.getElementById('detail-items-body');
        detailTbody.innerHTML = '';
        if (sale.sales_items && sale.sales_items.length > 0) {
            sale.sales_items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-800/10';
                tr.innerHTML = `
                    <td class="p-2">
                        <div class="font-semibold text-white">${item.product_name}</div>
                        <div class="text-[8px] text-gray-500 font-mono">${item.sku}</div>
                    </td>
                    <td class="p-2 text-center text-gray-400 font-semibold">${item.qty} ${item.unit || 'pcs'}</td>
                    <td class="p-2 text-right text-gray-400 font-mono">${formatRupiah(item.price)}</td>
                    <td class="p-2 text-right text-white font-semibold font-mono">${formatRupiah(item.qty * item.price)}</td>
                `;
                detailTbody.appendChild(tr);
            });
        } else {
            detailTbody.innerHTML = `<tr><td colspan="4" class="p-4 text-center text-gray-500">Tidak ada item dalam transaksi ini</td></tr>`;
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
