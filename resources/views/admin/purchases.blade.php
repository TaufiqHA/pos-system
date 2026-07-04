@extends('layouts.admin')

@section('title', 'Daftar Pembelian - Lucifer POS')

@section('page_title', 'Daftar Pembelian')
@section('page_subtitle', 'Mengelola transaksi pembelian barang / purchases')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <!-- Breadcrumbs or search space -->
        </div>
        <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Pembelian
        </button>
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
                    <th class="pb-3 px-4">Supplier</th>
                    {{-- <th class="pb-3 px-4">Cabang</th>
                    <th class="pb-3 px-4">Operator</th> --}}
                    <th class="pb-3 px-4">Total</th>
                    <th class="pb-3 px-4">Metode</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($purchases as $purchase)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white font-mono">{{ $purchase->invoice }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y H:i') }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $purchase->supplier?->name ?? '-' }}</td>
                        {{-- <td class="py-4 px-4 text-gray-400">{{ $purchase->branch?->name ?? '-' }}</td> --}}
                        {{-- <td class="py-4 px-4 text-gray-400">{{ $purchase->user?->name ?? '-' }}</td> --}}
                        <td class="py-4 px-4 text-white font-semibold">Rp {{ number_format($purchase->grand_total, 2, ',', '.') }}</td>
                        <td class="py-4 px-4 text-gray-400 font-semibold">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-400 uppercase">
                                {{ $purchase->purchasePayments->first()?->method ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold 
                                @if($purchase->status === 'completed' || $purchase->status === 'LUNAS') bg-green-500/20 text-green-400 
                                @elseif($purchase->status === 'pending' || $purchase->status === 'BELUM BAYAR') bg-yellow-500/20 text-yellow-400 
                                @else bg-red-500/20 text-red-400 @endif">
                                {{ strtoupper($purchase->status) }}
                            </span>
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($purchase) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($purchase) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
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
                        <td colspan="9" class="py-8 text-center text-gray-500">Belum ada transaksi pembelian terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH PEMBELIAN ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Pembelian Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat data transaksi pembelian baru</p>
        </div>
        <form action="{{ route('purchases.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <!-- Hidden inputs for branch and user operator -->
            <input type="hidden" name="branch_id" id="create-branch_id" value="{{ Auth::user()->branch_id ?? \App\Models\Branch::first()?->id }}">
            <input type="hidden" name="user_id" id="create-user_id" value="{{ Auth::user()->id }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-supplier_id" class="block font-bold text-gray-300">Supplier</label>
                    <select name="supplier_id" id="create-supplier_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <option value="">-- Pilih Supplier (Opsional) --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
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

            <!-- Section: Input Item Pembelian -->
            <div class="border-t border-gray-800 pt-4 mt-4">
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Pembelian</h4>
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

                <!-- Table of items -->
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
                    Simpan Pembelian
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT PEMBELIAN ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Pembelian</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data transaksi pembelian</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('purchases.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <!-- Hidden inputs for branch and user operator -->
            <input type="hidden" name="branch_id" id="edit-branch_id" value="{{ old('branch_id') }}">
            <input type="hidden" name="user_id" id="edit-user_id" value="{{ old('user_id') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-supplier_id" class="block font-bold text-gray-300">Supplier</label>
                    <select name="supplier_id" id="edit-supplier_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <option value="">-- Pilih Supplier (Opsional) --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
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

            <!-- Section: Input Item Pembelian -->
            <div class="border-t border-gray-800 pt-4 mt-4">
                <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Pembelian</h4>
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

                <!-- Table of items -->
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

<!-- ================= MODAL BOX: DETAIL PEMBELIAN ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Pembelian</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap transaksi pembelian</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Pembelian</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-[10px] select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">No. Invoice</p>
                    <p id="detail-invoice" class="text-white font-mono font-bold mt-0.5 text-xs select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Supplier</p>
                    <p id="detail-supplier" class="text-white mt-0.5 font-semibold"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Cabang</p>
                    <p id="detail-branch" class="text-white mt-0.5"></p>
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

            <!-- Section: Daftar Item Pembelian -->
            <div class="bg-gray-900/50 p-4 rounded-xl">
                <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px] mb-2">Item Pembelian</p>
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
                            <!-- JS will inject rows here -->
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
        // Auto hide success alert after 3 seconds
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

        // Auto open create/edit modal from query strings if present
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const id = urlParams.get('id');

        if (action === 'create') {
            openCreateModal();
        } else if (action === 'edit' && id) {
            const purchases = @json($purchases);
            const purchase = purchases.find(p => p.id === id);
            if (purchase) {
                openEditModal(purchase);
            }
        }

        // Auto-fill price when selecting a product
        document.getElementById('create-item-product').addEventListener('change', function() {
            const productId = this.value;
            const priceInput = document.getElementById('create-item-price');
            const qtyInput = document.getElementById('create-item-qty');
            
            const product = availableProducts.find(p => p.id === productId);
            if (product) {
                priceInput.value = formatNumberWithDots(product.buy_price);
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
                priceInput.value = formatNumberWithDots(product.buy_price);
                if (!qtyInput.value) qtyInput.value = 1;
            } else {
                priceInput.value = '';
            }
        });
    });

    // Helper to format numbers with indonesian style thousand separator
    function formatNumberWithDots(val) {
        const num = parseFloat(val) || 0;
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
    }

    // Helper to format inputs with dots on typing
    function formatInputWithDots(input, hiddenId, prefix) {
        let cleanVal = input.value.replace(/\D/g, '');
        if (!cleanVal) cleanVal = '0';
        const num = parseFloat(cleanVal);
        document.getElementById(hiddenId).value = num;
        input.value = formatNumberWithDots(num);
        calculateGrandTotal(prefix);
    }

    // Helper to update status based on selected payment method
    function updateStatusFromPaymentMethod(prefix) {
        const method = document.getElementById(`${prefix}-payment_method`).value;
        const statusInput = document.getElementById(`${prefix}-status`);
        if (method === 'TUNAI' || method === 'TRANSFER') {
            statusInput.value = 'LUNAS';
        } else if (method === 'KREDIT') {
            statusInput.value = 'BELUM BAYAR';
        }
    }

    // Helper to calculate grand total automatically
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

    // Purchase items handlers
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

        const price = parseFloat(product.buy_price) || 0;
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

    // Create Modal
    function openCreateModal() {
        createItems = [];
        renderItems('create');
        document.getElementById('create-discount').value = '0';
        document.getElementById('create-discount-display').value = '0';
        document.getElementById('create-tax').value = '0';
        document.getElementById('create-tax-display').value = '0';
        document.getElementById('create-subtotal-display').value = '0';
        document.getElementById('create-grand_total-display').value = '0';
        
        // Reset payment method and hidden status input
        document.getElementById('create-payment_method').value = 'TUNAI';
        document.getElementById('create-status').value = 'LUNAS';

        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Edit Modal
    function openEditModal(purchase) {
        document.getElementById('edit-id').value = purchase.id;
        document.getElementById('edit-supplier_id').value = purchase.supplier_id || '';
        document.getElementById('edit-branch_id').value = purchase.branch_id;
        document.getElementById('edit-user_id').value = purchase.user_id;
        
        // Format datetime-local input value (YYYY-MM-DDTHH:mm)
        if (purchase.date) {
            const dateObj = new Date(purchase.date);
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            const hours = String(dateObj.getHours()).padStart(2, '0');
            const minutes = String(dateObj.getMinutes()).padStart(2, '0');
            document.getElementById('edit-date').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        } else {
            document.getElementById('edit-date').value = '';
        }

        document.getElementById('edit-subtotal').value = purchase.subtotal;
        document.getElementById('edit-subtotal-display').value = formatNumberWithDots(purchase.subtotal);
        document.getElementById('edit-discount').value = purchase.discount;
        document.getElementById('edit-discount-display').value = formatNumberWithDots(purchase.discount);
        document.getElementById('edit-tax').value = purchase.tax;
        document.getElementById('edit-tax-display').value = formatNumberWithDots(purchase.tax);
        document.getElementById('edit-grand_total').value = purchase.grand_total;
        document.getElementById('edit-grand_total-display').value = formatNumberWithDots(purchase.grand_total);
        document.getElementById('edit-status').value = purchase.status;

        // Map status to payment method select value
        const methodSelect = document.getElementById('edit-payment_method');
        if (purchase.status === 'LUNAS') {
            methodSelect.value = 'TUNAI';
        } else if (purchase.status === 'BELUM BAYAR') {
            methodSelect.value = 'KREDIT';
        } else {
            // Fallbacks for older test data
            if (purchase.status === 'completed') {
                methodSelect.value = 'TUNAI';
                document.getElementById('edit-status').value = 'LUNAS';
            } else if (purchase.status === 'pending') {
                methodSelect.value = 'KREDIT';
                document.getElementById('edit-status').value = 'BELUM BAYAR';
            } else {
                methodSelect.value = 'TUNAI';
            }
        }
        
        // Populate editItems
        editItems = purchase.items ? purchase.items.map(item => ({
            product_id: item.product_id,
            product_name: item.product_name,
            sku: item.sku,
            qty: item.qty,
            price: parseFloat(item.price)
        })) : [];
        renderItems('edit');

        document.getElementById('edit-form').action = `/purchases/${purchase.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Detail Modal
    function openDetailModal(purchase) {
        document.getElementById('detail-id').textContent = purchase.id;
        document.getElementById('detail-invoice').textContent = purchase.invoice;
        document.getElementById('detail-supplier').textContent = purchase.supplier ? purchase.supplier.name : '-';
        document.getElementById('detail-branch').textContent = purchase.branch ? purchase.branch.name : '-';
        document.getElementById('detail-user').textContent = purchase.user ? purchase.user.name : '-';
        
        const purchaseDate = purchase.date ? new Date(purchase.date).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
        document.getElementById('detail-date').textContent = purchaseDate;

        // Rupiah Formatter Helper
        const formatRupiah = (val) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(val);
        };

        document.getElementById('detail-subtotal').textContent = formatRupiah(purchase.subtotal);
        document.getElementById('detail-discount').textContent = formatRupiah(purchase.discount);
        document.getElementById('detail-tax').textContent = formatRupiah(purchase.tax);
        document.getElementById('detail-grand_total').textContent = formatRupiah(purchase.grand_total);

        // Status Badge styling
        const statusEl = document.getElementById('detail-status');
        statusEl.textContent = purchase.status;
        statusEl.className = 'inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase ';
        if (purchase.status === 'completed') {
            statusEl.classList.add('bg-green-500/20', 'text-green-400');
        } else if (purchase.status === 'pending') {
            statusEl.classList.add('bg-yellow-500/20', 'text-yellow-400');
        } else {
            statusEl.classList.add('bg-red-500/20', 'text-red-400');
        }

        const createdDate = purchase.created_at ? new Date(purchase.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('detail-created').textContent = createdDate;

        // Render detail items
        const detailTbody = document.getElementById('detail-items-body');
        detailTbody.innerHTML = '';
        if (purchase.items && purchase.items.length > 0) {
            purchase.items.forEach(item => {
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
