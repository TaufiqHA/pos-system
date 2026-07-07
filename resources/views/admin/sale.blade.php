@extends('layouts.admin')

@section('title', 'Daftar Penjualan - Lucifer POS')

@section('page_title', 'Daftar Penjualan')
@section('page_subtitle', 'Mengelola transaksi penjualan barang')

@section('content')
    @php
        $selectedPoId = request('po_id');
        $selectedPo = $selectedPoId ? $purchaseOrders->firstWhere('id', $selectedPoId) : $purchaseOrders->first();
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;
        $showModal = request()->has('show_modal') || request()->has('po_id');
    @endphp
    <div class="card p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('sales.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice atau cabang..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
                <select name="wilayah_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach($wilayahs as $wilayah)
                        <option value="{{ $wilayah->id }}" {{ request('wilayah_id') == $wilayah->id ? 'selected' : '' }}>
                            {{ $wilayah->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="hidden">Search</button>
            </form>
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto justify-end">
                <button onclick="openPermintaanPoModal()"
                    class="w-full sm:w-auto justify-center border border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 cursor-pointer relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Permintaan PO
                    @php
                        $pendingCount = $purchaseOrders->where('status', 'Pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span
                            class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse border border-[#0B1120]">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </button>
                <button onclick="openCreateModal()"
                    class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Penjualan
                </button>
            </div>
        </div>

        @if (session('success'))
            <div id="success-alert"
                class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                            <td class="py-4 px-4 text-gray-400">{{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y H:i') }}
                            </td>
                            <td class="py-4 px-4 text-gray-400">{{ $sale->branch?->name ?? '-' }}</td>
                            <td class="py-4 px-4 text-white font-semibold">Rp
                                {{ number_format($sale->grand_total, 2, ',', '.') }}</td>
                            <td class="py-4 px-4 text-gray-400 font-semibold">
                                <span
                                    class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-400 uppercase">
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
                                    <button onclick="openDetailModal({{ json_encode($sale) }})"
                                        class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                        Detail
                                    </button>
                                    <button onclick="openEditModal({{ json_encode($sale) }})"
                                        class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                        Edit
                                    </button>
                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
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
    <div id="create-modal"
        class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
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
                        <label for="create-branch_id" class="block font-bold text-gray-300">Cabang Tujuan <span
                                class="text-red-500">*</span></label>
                        <select name="branch_id" id="create-branch_id"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="create-date" class="block font-bold text-gray-300">Tanggal Transaksi <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="date" id="create-date"
                            value="{{ old('date') ?? now()->format('Y-m-d\TH:i') }}"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                        @error('date')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-800 pt-4 mt-4">
                    <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3">
                        <div class="sm:col-span-2 flex gap-3 items-center">
                            <!-- Image Preview of Selected Product -->
                            <div class="w-11 h-11 rounded-xl bg-gray-900 border border-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                <img id="create-product-image-preview" src="" alt="" class="w-full h-full object-cover hidden">
                                <div id="create-product-image-placeholder" class="text-gray-600 text-[9px] uppercase font-bold">Image</div>
                            </div>
                            <div class="flex-1">
                                <select id="create-item-product"
                                    class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-image="{{ $product->image ?? '' }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <input type="number" id="create-item-qty" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="create-item-price" placeholder="Harga"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-2.5 focus:outline-none cursor-not-allowed"
                                readonly>
                            <button type="button" onclick="addItem('create')"
                                class="bg-[#B4F481] hover:bg-green-400 text-black px-3.5 rounded-xl font-bold cursor-pointer">+</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[150px] overflow-y-auto mb-4 border border-gray-800 rounded-xl">
                        <table class="w-full text-left border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-gray-900 text-gray-400 font-bold border-b border-gray-800">
                                    <th class="p-2 w-14 text-center">Gambar</th>
                                    <th class="p-2">Produk</th>
                                    <th class="p-2 text-center">Qty</th>
                                    <th class="p-2 text-right">Harga</th>
                                    <th class="p-2 text-right">Subtotal</th>
                                    <th class="p-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="create-items-body" class="divide-y divide-gray-800 text-gray-300">
                                <tr id="create-no-items">
                                    <td colspan="6" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="create-subtotal-display" class="block font-bold text-gray-300">Subtotal <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="create-subtotal-display" value="{{ old('subtotal') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="subtotal" id="create-subtotal" value="{{ old('subtotal') ?? 0 }}">
                        @error('subtotal')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="create-discount-display" class="block font-bold text-gray-300">Diskon</label>
                        <input type="text" id="create-discount-display" value="{{ old('discount') ?? '0' }}"
                            oninput="formatInputWithDots(this, 'create-discount', 'create-')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="discount" id="create-discount" value="{{ old('discount') ?? 0 }}">
                        @error('discount')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="create-tax-display" class="block font-bold text-gray-300">Pajak</label>
                        <input type="text" id="create-tax-display" value="{{ old('tax') ?? '0' }}"
                            oninput="formatInputWithDots(this, 'create-tax', 'create-')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="tax" id="create-tax" value="{{ old('tax') ?? 0 }}">
                        @error('tax')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="create-grand_total-display" class="block font-bold text-gray-300">Grand Total <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="create-grand_total-display" value="{{ old('grand_total') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="grand_total" id="create-grand_total"
                            value="{{ old('grand_total') ?? 0 }}">
                        @error('grand_total')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
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
                        @error('status')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()"
                        class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                        Simpan Penjualan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: EDIT PENJUALAN ================= -->
    <div id="edit-modal"
        class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Penjualan</h3>
                <p class="text-[11px] text-gray-400 mt-1">Ubah data transaksi penjualan</p>
            </div>
            <form id="edit-form" action="{{ old('id') ? route('sales.update', old('id')) : '#' }}" method="POST"
                class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

                <input type="hidden" name="user_id" id="edit-user_id" value="{{ old('user_id') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="edit-branch_id" class="block font-bold text-gray-300">Cabang Tujuan <span
                                class="text-red-500">*</span></label>
                        <select name="branch_id" id="edit-branch_id"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="edit-date" class="block font-bold text-gray-300">Tanggal Transaksi <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="date" id="edit-date" value="{{ old('date') }}"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
                        @error('date')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-800 pt-4 mt-4">
                    <h4 class="text-xs font-bold text-white mb-2 uppercase tracking-wider">Item Penjualan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3">
                        <div class="sm:col-span-2">
                            <select id="edit-item-product"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" id="edit-item-qty" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="edit-item-price" placeholder="Harga"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-2.5 focus:outline-none cursor-not-allowed"
                                readonly>
                            <button type="button" onclick="addItem('edit')"
                                class="bg-[#B4F481] hover:bg-green-400 text-black px-3.5 rounded-xl font-bold cursor-pointer">+</button>
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
                                <tr id="edit-no-items">
                                    <td colspan="5" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="edit-subtotal-display" class="block font-bold text-gray-300">Subtotal <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit-subtotal-display" value="{{ old('subtotal') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="subtotal" id="edit-subtotal" value="{{ old('subtotal') ?? 0 }}">
                        @error('subtotal')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="edit-discount-display" class="block font-bold text-gray-300">Diskon</label>
                        <input type="text" id="edit-discount-display" value="{{ old('discount') ?? '0' }}"
                            oninput="formatInputWithDots(this, 'edit-discount', 'edit-')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="discount" id="edit-discount" value="{{ old('discount') ?? 0 }}">
                        @error('discount')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="edit-tax-display" class="block font-bold text-gray-300">Pajak</label>
                        <input type="text" id="edit-tax-display" value="{{ old('tax') ?? '0' }}"
                            oninput="formatInputWithDots(this, 'edit-tax', 'edit-')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <input type="hidden" name="tax" id="edit-tax" value="{{ old('tax') ?? 0 }}">
                        @error('tax')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="edit-grand_total-display" class="block font-bold text-gray-300">Grand Total <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit-grand_total-display" value="{{ old('grand_total') ?? '0' }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl p-3 focus:outline-none cursor-not-allowed"
                            readonly required>
                        <input type="hidden" name="grand_total" id="edit-grand_total" value="{{ old('grand_total') ?? 0 }}">
                        @error('grand_total')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="edit-payment_method" class="block font-bold text-gray-300">Metode Pembayaran <span
                                class="text-red-500">*</span></label>
                        <select name="payment_method" id="edit-payment_method"
                            onchange="updateStatusFromPaymentMethod('edit')"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"
                            required>
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
                    <button type="button" onclick="closeEditModal()"
                        class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: DETAIL PENJUALAN ================= -->
    <div id="detail-modal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
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
                        <span id="detail-status"
                            class="inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase"></span>
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
                    <button onclick="closeDetailModal()"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition cursor-pointer">
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

        document.addEventListener('DOMContentLoaded', function () {
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

            document.getElementById('create-item-product').addEventListener('change', function () {
                const productId = this.value;
                const priceInput = document.getElementById('create-item-price');
                const qtyInput = document.getElementById('create-item-qty');
                const imgPreview = document.getElementById('create-product-image-preview');
                const imgPlaceholder = document.getElementById('create-product-image-placeholder');

                const product = availableProducts.find(p => p.id === productId);
                if (product) {
                    priceInput.value = formatNumberWithDots(product.sell_price);
                    if (!qtyInput.value) qtyInput.value = 1;

                    // Update image preview
                    if (product.image) {
                        imgPreview.src = product.image;
                        imgPreview.classList.remove('hidden');
                        imgPlaceholder.classList.add('hidden');
                    } else {
                        imgPreview.src = '';
                        imgPreview.classList.add('hidden');
                        imgPlaceholder.classList.remove('hidden');
                    }
                } else {
                    priceInput.value = '';
                    imgPreview.src = '';
                    imgPreview.classList.add('hidden');
                    imgPlaceholder.classList.remove('hidden');
                }
            });

            document.getElementById('edit-item-product').addEventListener('change', function () {
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
                    price: price,
                    image: product.image ?? ''
                });
            }

            productSelect.value = '';
            qtyInput.value = '';
            document.getElementById(`${prefix}-item-price`).value = '';

            if (prefix === 'create') {
                const imgPreview = document.getElementById('create-product-image-preview');
                const imgPlaceholder = document.getElementById('create-product-image-placeholder');
                if (imgPreview) {
                    imgPreview.src = '';
                    imgPreview.classList.add('hidden');
                }
                if (imgPlaceholder) {
                    imgPlaceholder.classList.remove('hidden');
                }
            }

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

            const isCreate = prefix === 'create';
            const colspan = isCreate ? 6 : 5;

            if (itemsArray.length === 0) {
                tbody.innerHTML = `<tr id="${prefix}-no-items"><td colspan="${colspan}" class="p-4 text-center text-gray-500">Belum ada item ditambahkan</td></tr>`;
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

                let imgTdHtml = '';
                if (isCreate) {
                    let imgHtml = '';
                    if (item.image) {
                        imgHtml = `<img src="${item.image}" alt="${item.product_name}" class="w-10 h-10 rounded-lg object-cover border border-gray-800/80 shadow-md mx-auto">`;
                    } else {
                        imgHtml = `<div class="w-10 h-10 bg-gray-850 border border-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-[9px] font-semibold uppercase mx-auto">No Img</div>`;
                    }
                    imgTdHtml = `<td class="p-2"><div class="flex justify-center">${imgHtml}</div></td>`;
                }

                tr.innerHTML = `
                    ${imgTdHtml}
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
            const select = document.getElementById('create-item-product');
            if (select) select.value = '';
            const qtyInput = document.getElementById('create-item-qty');
            if (qtyInput) qtyInput.value = '';
            const priceInput = document.getElementById('create-item-price');
            if (priceInput) priceInput.value = '';

            const imgPreview = document.getElementById('create-product-image-preview');
            const imgPlaceholder = document.getElementById('create-product-image-placeholder');
            if (imgPreview) {
                imgPreview.src = '';
                imgPreview.classList.add('hidden');
            }
            if (imgPlaceholder) {
                imgPlaceholder.classList.remove('hidden');
            }
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
            if (sale.sales_payments && sale.sales_payments.length > 0) {
                methodSelect.value = sale.sales_payments[0].method;
            } else if (sale.status === 'LUNAS') {
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

            document.getElementById('edit-form').action = `/auth/sales/${sale.id}`;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function openDetailModal(sale) {
            document.getElementById('detail-id').textContent = sale.id;
            document.getElementById('detail-invoice').textContent = sale.invoice;
            let branchUserName = '-';
            if (sale.branch && sale.branch.users && sale.branch.users.length) {
                branchUserName = sale.branch.users[0].name;
            }
            document.getElementById('detail-branch').textContent = branchUserName;
            // document.getElementById('detail-user').textContent = sale.user ? sale.user.name : '-';

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

        function openPermintaanPoModal() {
            document.getElementById('permintaan-po-modal').classList.remove('hidden');
        }

        function closePermintaanPoModal() {
            window.location.href = "{{ route('sales.index') }}";
        }

        function submitApprovalForm(status) {
            if (!confirm(`Apakah Anda yakin ingin menandai PO ini sebagai ${status === 'Approved' ? 'SETUJU' : 'DITOLAK'}?`)) {
                return;
            }
            document.getElementById('form-status').value = status;
            document.getElementById('approval-form').submit();
        }
    </script>

    <!-- ================= MODAL BOX: PERMINTAAN PO ================= -->
    <div id="permintaan-po-modal"
        class="fixed inset-0 z-50 @if(!$showModal) hidden @endif bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
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
                <h3 class="text-base font-bold tracking-wide font-display text-white">Daftar Permintaan PO Cabang</h3>
                <p class="text-[11px] text-gray-400 mt-1">Daftar pengajuan Purchase Order dari cabang yang terhubung</p>
            </div>

            <!-- Modal Content - Split Screen Grid -->
            <div class="flex-1 flex overflow-hidden min-h-0">
                <!-- Side Kiri: Daftar PO -->
                <div class="w-1/3 border-r border-gray-800 overflow-y-auto bg-gray-950/40 p-4 space-y-3">
                    <h4 class="text-[10px] font-bold tracking-wider text-gray-550 uppercase px-2">Pilih Permintaan PO</h4>
                    <div class="space-y-2" id="po-list-container">
                        @forelse($purchaseOrders as $po)
                            @php
                                $notes = json_decode($po->notes, true);
                                $grandTotal = $notes['grand_total'] ?? 0;
                            @endphp
                            <a href="?po_id={{ $po->id }}&show_modal=1" id="po-item-{{ $po->id }}"
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
                                        {{ $po->branch->name ?? ($po->user->name ?? 'Cabang') }}</div>
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
                                class="w-16 h-16 rounded-2xl bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-505 mb-4 animate-bounce">
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
                            <input type="hidden" name="branch_id" value="{{ $selectedPo->branch_id }}">
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
                                <!-- Cabang (Readonly) -->
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-300">Cabang *</label>
                                    <input type="text" readonly
                                        value="{{ $selectedPo->branch->name ?? ($selectedPo->user->name ?? 'Cabang') }}"
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
                                                    $price = floatval($item['price'] ?? 0);
                                                    $qty = intval($item['qty'] ?? 0);
                                                    $subtotal = $price * $qty;
                                                @endphp
                                                <tr class="border-b border-gray-800 hover:bg-gray-800/20 transition">
                                                    <td class="py-3 px-4">
                                                        <div class="font-bold text-white">{{ $item['name'] ?? '' }}</div>
                                                        <div class="text-[10px] text-gray-500 font-medium">SKU: {{ $item['sku'] ?? '' }}</div>
                                                    </td>
                                                    <td class="py-3 px-4 text-center text-white font-semibold">{{ $qty }}</td>
                                                    <td class="py-3 px-4 text-right text-white">Rp
                                                        {{ number_format($price, 0, ',', '.') }}</td>
                                                    <td class="py-3 px-4 text-right text-white font-bold">Rp
                                                        {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-500">Tidak ada item dalam PO
                                                        ini</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Ringkasan Finansial (Subtotal, Diskon, Pajak) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-800 pt-4">
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Subtotal</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($selectedPoNotes['subtotal'] ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Diskon</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($selectedPoNotes['discount'] ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Pajak</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($selectedPoNotes['tax'] ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
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
                                <label class="block font-bold text-gray-355">Catatan / Notes Cabang</label>
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
@endsection