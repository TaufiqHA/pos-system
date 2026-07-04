@extends('layouts.admin')

@section('title', 'Daftar Produk - Lucifer POS')

@section('page_title', 'Daftar Produk')
@section('page_subtitle', 'Mengelola persediaan produk POS')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex justify-end items-center mb-6">
        <button onclick="openCreateModal()" class="bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Produk
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4">SKU</th>
                    <th class="pb-3">Nama Produk</th>
                    <th class="pb-3">Kategori</th>
                    <th class="pb-3 text-right">Harga Jual</th>
                    <th class="pb-3 text-right pr-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 font-mono text-gray-400">{{ $product->sku }}</td>
                        <td class="py-4 font-semibold text-white">
                            <div>
                                <span class="block">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="bg-indigo-900/40 text-indigo-300 border border-indigo-800/50 py-0.5 px-2 rounded-full text-[10px] font-semibold">
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-4 text-right font-bold text-[#B4F481]">
                            Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 text-right pr-4">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($product->load('category')) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($product) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
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
                        <td colspan="6" class="py-8 text-center text-gray-500">Belum ada produk terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH PRODUK ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Produk Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat produk baru dengan detail harga dan SKU</p>
        </div>
        <form action="{{ route('products.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-category_id" class="block font-bold text-gray-300">Kategori *</label>
                    <select name="category_id" id="create-category_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('category_id') && !old('_method')) border-red-500 @endif">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (!old('_method') && old('category_id') == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('category_id') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('category_id') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-sku" class="block font-bold text-gray-300">SKU *</label>
                    <input type="text" name="sku" id="create-sku" value="{{ !old('_method') ? old('sku') : '' }}" placeholder="Contoh: PRD-001" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sku') && !old('_method')) border-red-500 @endif">
                    @if($errors->has('sku') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('sku') }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-name" class="block font-bold text-gray-300">Nama Produk *</label>
                    <input type="text" name="name" id="create-name" value="{{ !old('_method') ? old('name') : '' }}" placeholder="Contoh: Kopi Bubuk" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('name') && !old('_method')) border-red-500 @endif">
                    @if($errors->has('name') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-unit" class="block font-bold text-gray-300">Satuan</label>
                    <input type="text" name="unit" id="create-unit" value="{{ !old('_method') ? old('unit') : '' }}" placeholder="Contoh: pcs, pack" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label for="create-buy_price" class="block font-bold text-gray-300">Harga Beli *</label>
                    <input type="number" step="0.01" name="buy_price" id="create-buy_price" value="{{ !old('_method') ? old('buy_price', 0) : 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('buy_price') && !old('_method')) border-red-500 @endif">
                    @if($errors->has('buy_price') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('buy_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-sell_price" class="block font-bold text-gray-300">Harga Jual *</label>
                    <input type="number" step="0.01" name="sell_price" id="create-sell_price" value="{{ !old('_method') ? old('sell_price', 0) : 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sell_price') && !old('_method')) border-red-500 @endif">
                    @if($errors->has('sell_price') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('sell_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-is_wholesale" class="block font-bold text-gray-300">Grosir? *</label>
                    <select name="is_wholesale" id="create-is_wholesale" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <option value="0" {{ (!old('_method') && old('is_wholesale') == '0') ? 'selected' : '' }}>Bukan Grosir</option>
                        <option value="1" {{ (!old('_method') && old('is_wholesale') == '1') ? 'selected' : '' }}>Grosir</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label for="create-description" class="block font-bold text-gray-300">Deskripsi</label>
                <textarea name="description" id="create-description" rows="2" placeholder="Deskripsi produk..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ !old('_method') ? old('description') : '' }}</textarea>
            </div>

            <div class="space-y-1">
                <label for="create-image" class="block font-bold text-gray-300">URL Gambar</label>
                <input type="text" name="image" id="create-image" value="{{ !old('_method') ? old('image') : '' }}" placeholder="https://example.com/image.jpg" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-800">
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT PRODUK ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Produk</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah detail produk yang sudah ada</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('products.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-category_id" class="block font-bold text-gray-300">Kategori *</label>
                    <select name="category_id" id="edit-category_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('category_id') && old('_method') === 'PUT') border-red-500 @endif">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('_method') === 'PUT' && old('category_id') == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('category_id') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('category_id') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-sku" class="block font-bold text-gray-300">SKU *</label>
                    <input type="text" name="sku" id="edit-sku" value="{{ old('_method') === 'PUT' ? old('sku') : '' }}" placeholder="Contoh: PRD-001" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sku') && old('_method') === 'PUT') border-red-500 @endif">
                    @if($errors->has('sku') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('sku') }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-name" class="block font-bold text-gray-300">Nama Produk *</label>
                    <input type="text" name="name" id="edit-name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" placeholder="Contoh: Kopi Bubuk" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('name') && old('_method') === 'PUT') border-red-500 @endif">
                    @if($errors->has('name') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-unit" class="block font-bold text-gray-300">Satuan</label>
                    <input type="text" name="unit" id="edit-unit" value="{{ old('_method') === 'PUT' ? old('unit') : '' }}" placeholder="Contoh: pcs, pack" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label for="edit-buy_price" class="block font-bold text-gray-300">Harga Beli *</label>
                    <input type="number" step="0.01" name="buy_price" id="edit-buy_price" value="{{ old('_method') === 'PUT' ? old('buy_price', 0) : 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('buy_price') && old('_method') === 'PUT') border-red-500 @endif">
                    @if($errors->has('buy_price') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('buy_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-sell_price" class="block font-bold text-gray-300">Harga Jual *</label>
                    <input type="number" step="0.01" name="sell_price" id="edit-sell_price" value="{{ old('_method') === 'PUT' ? old('sell_price', 0) : 0 }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sell_price') && old('_method') === 'PUT') border-red-500 @endif">
                    @if($errors->has('sell_price') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('sell_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-is_wholesale" class="block font-bold text-gray-300">Grosir? *</label>
                    <select name="is_wholesale" id="edit-is_wholesale" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                        <option value="0" {{ (old('_method') === 'PUT' && old('is_wholesale') == '0') ? 'selected' : '' }}>Bukan Grosir</option>
                        <option value="1" {{ (old('_method') === 'PUT' && old('is_wholesale') == '1') ? 'selected' : '' }}>Grosir</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label for="edit-description" class="block font-bold text-gray-300">Deskripsi</label>
                <textarea name="description" id="edit-description" rows="2" placeholder="Deskripsi produk..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ old('_method') === 'PUT' ? old('description') : '' }}</textarea>
            </div>

            <div class="space-y-1">
                <label for="edit-image" class="block font-bold text-gray-300">URL Gambar</label>
                <input type="text" name="image" id="edit-image" value="{{ old('_method') === 'PUT' ? old('image') : '' }}" placeholder="https://example.com/image.jpg" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-800">
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: DETAIL PRODUK ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Produk</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap detail produk</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs mb-6">
            <!-- Kolom Gambar -->
            <div class="md:col-span-1 flex flex-col items-center justify-center p-4 bg-gray-900/50 rounded-2xl border border-gray-800">
                <img id="detail-image-el" src="" alt="Produk" class="max-h-40 rounded-xl object-contain mb-2 hidden">
                <div id="detail-image-fallback" class="w-full h-40 bg-gray-800 flex items-center justify-center rounded-xl text-gray-500 text-[10px] font-bold">
                    TIDAK ADA GAMBAR
                </div>
                <span id="detail-sku-label" class="text-gray-400 font-mono mt-2 font-bold"></span>
            </div>

            <!-- Kolom Info -->
            <div class="md:col-span-2 space-y-4 bg-gray-900/50 p-6 rounded-2xl border border-gray-800">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Nama Produk</span>
                        <span id="detail-name" class="text-white text-sm font-bold block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Kategori</span>
                        <span id="detail-category" class="inline-block bg-indigo-900/40 text-indigo-300 border border-indigo-800/50 py-0.5 px-2 rounded-full font-semibold mt-1"></span>
                    </div>
                </div>



                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Harga Beli</span>
                        <span id="detail-buy_price" class="text-white font-bold block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Harga Jual</span>
                        <span id="detail-sell_price" class="text-[#B4F481] font-bold block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Satuan</span>
                        <span id="detail-unit" class="text-white block mt-0.5"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Grosir?</span>
                        <span id="detail-wholesale" class="text-white block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Status Penghapusan</span>
                        <span id="detail-status" class="text-white block mt-0.5"></span>
                    </div>
                </div>

                <div>
                    <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Deskripsi</span>
                    <p id="detail-description" class="text-white mt-1 leading-relaxed bg-gray-900 p-3 rounded-xl border border-gray-800/80"></p>
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end border-t border-gray-800">
            <button onclick="closeDetailModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // Create Modal
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    // Edit Modal
    function openEditModal(product) {
        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-category_id').value = product.category_id;
        document.getElementById('edit-sku').value = product.sku;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-unit').value = product.unit || '';
        document.getElementById('edit-buy_price').value = product.buy_price;
        document.getElementById('edit-sell_price').value = product.sell_price;
        document.getElementById('edit-is_wholesale').value = product.is_wholesale ? '1' : '0';
        document.getElementById('edit-description').value = product.description || '';
        document.getElementById('edit-image').value = product.image || '';
        
        document.getElementById('edit-form').action = `/products/${product.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    // Detail Modal
    function openDetailModal(product) {
        document.getElementById('detail-sku-label').textContent = product.sku;
        document.getElementById('detail-name').textContent = product.name;
        document.getElementById('detail-category').textContent = product.category ? product.category.name : '-';
        
        // Formatting Prices
        const buyFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(product.buy_price);
        const sellFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(product.sell_price);
        
        document.getElementById('detail-buy_price').textContent = buyFormatted;
        document.getElementById('detail-sell_price').textContent = sellFormatted;
        document.getElementById('detail-unit').textContent = product.unit || '-';
        document.getElementById('detail-wholesale').textContent = product.is_wholesale ? 'Ya (Grosir)' : 'Tidak (Eceran)';
        document.getElementById('detail-status').textContent = product.deleted_at ? 'Terhapus (Soft Delete)' : 'Aktif';
        document.getElementById('detail-description').textContent = product.description || 'Tidak ada deskripsi.';
        
        // Image Elements
        const imageEl = document.getElementById('detail-image-el');
        const imageFallback = document.getElementById('detail-image-fallback');
        if (product.image) {
            imageEl.src = product.image;
            imageEl.classList.remove('hidden');
            imageFallback.classList.add('hidden');
        } else {
            imageEl.classList.add('hidden');
            imageFallback.classList.remove('hidden');
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
