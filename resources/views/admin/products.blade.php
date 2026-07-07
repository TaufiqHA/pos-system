@extends('layouts.admin')

@section('title', 'Daftar Produk - Lucifer POS')

@section('page_title', 'Daftar Produk')
@section('page_subtitle', 'Mengelola persediaan produk POS')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
            <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="hidden">Search</button>
        </form>

        <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Produk
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
                    <th class="pb-3 pl-4 pr-2 w-12 text-center">Gambar</th>
                    <th class="pb-3 px-4">SKU</th>
                    <th class="pb-3 px-4">Nama Produk</th>
                    <th class="pb-3 px-4">Kategori</th>
                    <th class="pb-3 px-4 text-right">Harga Beli</th>
                    <th class="pb-3 px-4 text-right">Harga Jual</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-3 pl-4 pr-2 align-middle text-center">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-800/80 shadow-md mx-auto hover:scale-105 transition duration-200">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-900/50 border border-gray-800 flex items-center justify-center text-gray-600 text-[9px] font-bold mx-auto">
                                    N/A
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-gray-400 align-middle">{{ $product->sku }}</td>
                        <td class="py-3 px-4 font-semibold text-white align-middle">
                            <div>
                                <span class="block">{{ $product->name }}</span>
                                @if($product->is_wholesale)
                                    <span class="inline-block bg-green-950/20 text-[#B4F481] border border-green-800/50 py-0.5 px-2 rounded-full text-[9px] font-semibold mt-1">Grosir</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            <span class="bg-indigo-900/40 text-indigo-300 border border-indigo-800/50 py-0.5 px-2 rounded-full text-[10px] font-semibold">
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-gray-400 font-medium align-middle">
                            Rp {{ number_format($product->buy_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-[#B4F481] align-middle">
                            Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 pl-4 pr-4 text-right whitespace-nowrap align-middle">
                            <div class="flex justify-end items-center gap-2">
                                @if($product->is_wholesale)
                                    <button onclick="openWholesaleModal('{{ $product->id }}')" class="text-green-400 hover:text-green-300 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                        Harga Grosir
                                    </button>
                                @endif
                                <button onclick="openDetailModal('{{ $product->id }}')" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal('{{ $product->id }}')" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
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
                        <td colspan="7" class="py-8 text-center text-gray-500">Belum ada produk terdaftar.</td>
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
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
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
                    <span id="create-sku-message" class="text-[10px] mt-1 hidden"></span>
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
                    <input type="text" name="buy_price" id="create-buy_price" value="{{ !old('_method') ? old('buy_price', 0) : 0 }}" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('buy_price') && !old('_method')) border-red-500 @endif">
                    @if($errors->has('buy_price') && !old('_method'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('buy_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-sell_price" class="block font-bold text-gray-300">Harga Jual *</label>
                    <input type="text" name="sell_price" id="create-sell_price" value="{{ !old('_method') ? old('sell_price', 0) : 0 }}" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sell_price') && !old('_method')) border-red-500 @endif">
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

            <div class="space-y-2">
                <label for="create-image" class="block font-bold text-gray-300">Gambar Produk</label>
                <div class="flex items-center gap-4">
                    <div id="create-preview-container" class="w-20 h-20 bg-gray-900 border border-gray-800 rounded-xl flex items-center justify-center overflow-hidden">
                        <span id="create-preview-placeholder" class="text-[10px] text-gray-500 font-semibold text-center px-1">Belum ada gambar</span>
                        <img id="create-preview-img" class="w-full h-full object-cover hidden" src="" alt="Preview">
                    </div>
                    <div class="flex-1">
                        <input type="file" name="image" id="create-image" accept="image/*" class="hidden" onchange="previewImage(this, 'create-preview-img', 'create-preview-placeholder')">
                        <label for="create-image" class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white rounded-xl py-2 px-4 font-semibold cursor-pointer transition text-xs shadow-md">
                            Pilih File Gambar
                        </label>
                        <p class="text-[10px] text-gray-500 mt-1.5">Format: JPG, JPEG, PNG, GIF, SVG (Maks. 2MB)</p>
                    </div>
                </div>
                @if($errors->has('image') && !old('_method'))
                    <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('image') }}</p>
                @endif
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-800">
                <button type="button" onclick="closeCreateModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
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
        <form id="edit-form" action="{{ old('id') ? route('products.update', old('id')) : '#' }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
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
                    <span id="edit-sku-message" class="text-[10px] mt-1 hidden"></span>
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
                    <input type="text" name="buy_price" id="edit-buy_price" value="{{ old('_method') === 'PUT' ? old('buy_price', 0) : 0 }}" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('buy_price') && old('_method') === 'PUT') border-red-500 @endif">
                    @if($errors->has('buy_price') && old('_method') === 'PUT')
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('buy_price') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-sell_price" class="block font-bold text-gray-300">Harga Jual *</label>
                    <input type="text" name="sell_price" id="edit-sell_price" value="{{ old('_method') === 'PUT' ? old('sell_price', 0) : 0 }}" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('sell_price') && old('_method') === 'PUT') border-red-500 @endif">
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

            <div class="space-y-2">
                <label for="edit-image" class="block font-bold text-gray-300">Gambar Produk</label>
                <div class="flex items-center gap-4">
                    <div id="edit-preview-container" class="w-20 h-20 bg-gray-900 border border-gray-800 rounded-xl flex items-center justify-center overflow-hidden">
                        <span id="edit-preview-placeholder" class="text-[10px] text-gray-500 font-semibold text-center px-1">Belum ada gambar</span>
                        <img id="edit-preview-img" class="w-full h-full object-cover hidden" src="" alt="Preview">
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" name="image" id="edit-image" accept="image/*" class="hidden" onchange="previewImage(this, 'edit-preview-img', 'edit-preview-placeholder')">
                        <div class="flex items-center gap-2">
                            <label for="edit-image" class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white rounded-xl py-2 px-4 font-semibold cursor-pointer transition text-xs shadow-md">
                                Ubah Gambar
                            </label>
                            <button type="button" id="edit-clear-image-btn" onclick="clearEditImage()" class="hidden inline-flex items-center justify-center bg-red-950/20 hover:bg-red-900/30 border border-red-800/50 text-red-400 rounded-xl py-2 px-4 font-semibold cursor-pointer transition text-xs shadow-md">
                                Hapus Gambar
                            </button>
                        </div>
                        <input type="hidden" name="delete_image" id="edit-delete-image" value="0">
                        <p class="text-[10px] text-gray-500">Format: JPG, JPEG, PNG, GIF, SVG (Maks. 2MB)</p>
                    </div>
                </div>
                @if($errors->has('image') && old('_method') === 'PUT')
                    <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('image') }}</p>
                @endif
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-800">
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



                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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

                <div id="detail-wholesale-section" class="hidden">
                    <span class="text-gray-500 font-bold uppercase tracking-wider text-[9px] block">Daftar Harga Grosir</span>
                    <div class="mt-1 bg-gray-900 p-3 rounded-xl border border-gray-800/80 overflow-x-auto max-h-40">
                        <table class="w-full text-left border-collapse text-[10px]">
                            <thead>
                                <tr class="border-b border-gray-850 text-gray-500">
                                    <th class="pb-1 font-bold">Cabang</th>
                                    <th class="pb-1 text-center font-bold">Min. Qty</th>
                                    <th class="pb-1 text-right font-bold pr-2">Harga Grosir</th>
                                </tr>
                            </thead>
                            <tbody id="detail-wholesale-list" class="divide-y divide-gray-800/40 text-gray-300">
                            </tbody>
                        </table>
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

<!-- ================= MODAL BOX: KELOLA HARGA GROSIR ================= -->
<div id="wholesale-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeWholesaleModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Kelola Harga Grosir</h3>
            <p class="text-[11px] text-gray-400 mt-1">Mengelola daftar harga grosir berdasarkan cabang dan kuantitas minimum untuk produk: <span id="wholesale-product-name" class="font-bold text-[#B4F481]"></span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
            <!-- Form Tambah Harga Grosir -->
            <div class="md:col-span-1 p-4 bg-gray-900/50 rounded-2xl border border-gray-800 space-y-4">
                <h4 class="text-white font-bold text-xs border-b border-gray-800 pb-2">Tambah Harga Grosir</h4>
                <form id="wholesale-form" onsubmit="submitWholesaleForm(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" id="wholesale-product-id">
                    
                    <input type="hidden" name="branch_id" id="wholesale-branch-id" value="{{ auth()->user()->branch_id ?? '' }}">

                    <div class="space-y-1">
                        <label for="wholesale-min-qty" class="block font-bold text-gray-300">Min. Qty *</label>
                        <input type="number" name="min_qty" id="wholesale-min-qty" min="1" required placeholder="Contoh: 10" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>

                    <div class="space-y-1">
                        <label for="wholesale-price" class="block font-bold text-gray-300">Harga Satuan Grosir *</label>
                        <input type="text" name="price" id="wholesale-price" required oninput="formatRupiah(this)" placeholder="Contoh: 45.000" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-4 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Harga
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Harga Grosir Aktif -->
            <div class="md:col-span-2 p-4 bg-gray-900/50 rounded-2xl border border-gray-800 flex flex-col">
                <h4 class="text-white font-bold text-xs border-b border-gray-800 pb-2 mb-3">Daftar Harga Grosir Terdaftar</h4>
                <div class="overflow-x-auto flex-1 max-h-64">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-[10px] font-bold uppercase tracking-wider">
                                <th class="pb-2">Cabang</th>
                                <th class="pb-2 text-center">Min. Qty</th>
                                <th class="pb-2 text-right">Harga Grosir</th>
                                <th class="pb-2 text-right pr-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wholesale-list-body" class="divide-y divide-gray-800 text-gray-300">
                            <!-- Diisi dinamis lewat JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pt-4 mt-6 flex items-center justify-end border-t border-gray-800">
            <button onclick="closeWholesaleModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                Selesai
            </button>
        </div>
    </div>
</div>

<script>
    // Master data produk & cabang dari server
    let productsData = @json($products);
    const branchesData = @json($branches);

    function formatRupiah(element) {
        // Hapus karakter selain angka
        let value = element.value.replace(/[^0-9]/g, '');
        
        // Tambahkan titik setiap 3 angka (format ribuan Indonesia)
        if (value) {
            element.value = parseInt(value, 10).toLocaleString('id-ID').replace(/,/g, '.');
        } else {
            element.value = '';
        }
    }

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

        // Auto-format any pre-filled/validation-failed price inputs on load
        const priceInputsOnLoad = document.querySelectorAll('input[name="buy_price"], input[name="sell_price"]');
        priceInputsOnLoad.forEach(input => {
            if (input.value) {
                formatRupiah(input);
            }
        });

        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const priceInputs = form.querySelectorAll('input[name="buy_price"], input[name="sell_price"]');
                priceInputs.forEach(input => {
                    if (input.value) {
                        // Hapus semua titik agar menjadi angka murni untuk dikirim ke backend
                        input.value = input.value.replace(/\./g, '');
                    }
                });
            });
        });
    });

    // Image Upload Preview and Action Functions
    function previewImage(input, imgId, placeholderId) {
        const img = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
            
            if (imgId === 'edit-preview-img') {
                document.getElementById('edit-clear-image-btn').classList.remove('hidden');
                document.getElementById('edit-delete-image').value = '0';
            }
        }
    }

    function clearEditImage() {
        const img = document.getElementById('edit-preview-img');
        const placeholder = document.getElementById('edit-preview-placeholder');
        const fileInput = document.getElementById('edit-image');
        const clearBtn = document.getElementById('edit-clear-image-btn');
        const deleteImageInput = document.getElementById('edit-delete-image');
        
        fileInput.value = '';
        img.src = '';
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
        clearBtn.classList.add('hidden');
        deleteImageInput.value = '1';
    }

    // Create Modal
    function openCreateModal() {
        // Reset SKU checking states
        const createSkuMessage = document.getElementById('create-sku-message');
        if (createSkuMessage) {
            createSkuMessage.classList.add('hidden');
        }
        const createSkuInput = document.getElementById('create-sku');
        if (createSkuInput) {
            createSkuInput.classList.remove('border-red-500');
        }
        
        // Reset Image file input and preview
        const createPreviewImg = document.getElementById('create-preview-img');
        const createPreviewPlaceholder = document.getElementById('create-preview-placeholder');
        const createImgInput = document.getElementById('create-image');
        if (createPreviewImg) {
            createPreviewImg.src = '';
            createPreviewImg.classList.add('hidden');
        }
        if (createPreviewPlaceholder) {
            createPreviewPlaceholder.classList.remove('hidden');
        }
        if (createImgInput) {
            createImgInput.value = '';
        }

        const createForm = document.querySelector('#create-modal form');
        if (createForm) {
            const submitBtn = createForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    // Edit Modal
    function openEditModal(id) {
        const product = productsData.find(p => p.id === id);
        if (!product) return;

        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-category_id').value = product.category_id;
        document.getElementById('edit-sku').value = product.sku;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-unit').value = product.unit || '';
        document.getElementById('edit-buy_price').value = product.buy_price ? parseInt(product.buy_price, 10).toLocaleString('id-ID').replace(/,/g, '.') : '0';
        document.getElementById('edit-sell_price').value = product.sell_price ? parseInt(product.sell_price, 10).toLocaleString('id-ID').replace(/,/g, '.') : '0';
        document.getElementById('edit-is_wholesale').value = product.is_wholesale ? '1' : '0';
        document.getElementById('edit-description').value = product.description || '';
        
        // Reset file input & delete flag
        document.getElementById('edit-image').value = '';
        document.getElementById('edit-delete-image').value = '0';
        
        // Setup existing image preview
        const editPreviewImg = document.getElementById('edit-preview-img');
        const editPreviewPlaceholder = document.getElementById('edit-preview-placeholder');
        const editClearBtn = document.getElementById('edit-clear-image-btn');
        
        if (product.image) {
            editPreviewImg.src = product.image;
            editPreviewImg.classList.remove('hidden');
            editPreviewPlaceholder.classList.add('hidden');
            editClearBtn.classList.remove('hidden');
        } else {
            editPreviewImg.src = '';
            editPreviewImg.classList.add('hidden');
            editPreviewPlaceholder.classList.remove('hidden');
            editClearBtn.classList.add('hidden');
        }
        
        // Reset SKU checking states
        const editSkuMessage = document.getElementById('edit-sku-message');
        if (editSkuMessage) {
            editSkuMessage.classList.add('hidden');
        }
        const editSkuInput = document.getElementById('edit-sku');
        if (editSkuInput) {
            editSkuInput.classList.remove('border-red-500');
        }
        const editForm = document.getElementById('edit-form');
        if (editForm) {
            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        document.getElementById('edit-form').action = `/admin/products/${product.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    // Detail Modal
    function openDetailModal(id) {
        const product = productsData.find(p => p.id === id);
        if (!product) return;

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

        // Wholesale prices section
        const wholesaleSection = document.getElementById('detail-wholesale-section');
        const wholesaleList = document.getElementById('detail-wholesale-list');
        wholesaleList.innerHTML = '';

        if (product.is_wholesale && product.wholesale_prices && product.wholesale_prices.length > 0) {
            wholesaleSection.classList.remove('hidden');
            product.wholesale_prices.forEach(wp => {
                const branchName = wp.branch ? wp.branch.name : 'Semua Cabang';
                const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(wp.price);
                const row = `
                    <tr class="border-b border-gray-800/20">
                        <td class="py-1.5">${branchName}</td>
                        <td class="py-1.5 text-center font-bold">${wp.min_qty}</td>
                        <td class="py-1.5 text-right font-bold text-[#B4F481] pr-2">${formattedPrice}</td>
                    </tr>
                `;
                wholesaleList.insertAdjacentHTML('beforeend', row);
            });
        } else {
            wholesaleSection.classList.add('hidden');
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }

    // Wholesale Price Modal Functions
    let currentWholesaleProductId = null;
    let currentWholesalePrices = [];

    function openWholesaleModal(productId) {
        const product = productsData.find(p => p.id === productId);
        if (!product) return;

        currentWholesaleProductId = productId;
        currentWholesalePrices = product.wholesale_prices || [];

        document.getElementById('wholesale-product-id').value = productId;
        document.getElementById('wholesale-product-name').textContent = `${product.name} (${product.sku})`;
        
        // Reset form
        document.getElementById('wholesale-form').reset();

        renderWholesalePricesList();

        document.getElementById('wholesale-modal').classList.remove('hidden');
    }

    function closeWholesaleModal() {
        document.getElementById('wholesale-modal').classList.add('hidden');
    }

    function renderWholesalePricesList() {
        const listBody = document.getElementById('wholesale-list-body');
        listBody.innerHTML = '';

        if (currentWholesalePrices.length === 0) {
            listBody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-4 text-center text-gray-500">Belum ada harga grosir untuk produk ini.</td>
                </tr>
            `;
            return;
        }

        // Sort by branch name and min_qty
        const sortedPrices = [...currentWholesalePrices].sort((a, b) => {
            const nameA = (a.branch ? a.branch.name : '').toLowerCase();
            const nameB = (b.branch ? b.branch.name : '').toLowerCase();
            if (nameA !== nameB) return nameA.localeCompare(nameB);
            return a.min_qty - b.min_qty;
        });

        sortedPrices.forEach(wp => {
            const branchName = wp.branch ? wp.branch.name : 'Semua Cabang';
            const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(wp.price);
            
            const row = `
                <tr class="hover:bg-gray-805/30 transition">
                    <td class="py-2.5">${branchName}</td>
                    <td class="py-2.5 text-center font-bold">${wp.min_qty}</td>
                    <td class="py-2.5 text-right font-bold text-[#B4F481]">${formattedPrice}</td>
                    <td class="py-2.5 text-right pr-2">
                        <button type="button" onclick="deleteWholesalePrice('${wp.id}')" class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-0.5 hover:bg-red-500/10 rounded cursor-pointer">
                            Hapus
                        </button>
                    </td>
                </tr>
            `;
            listBody.insertAdjacentHTML('beforeend', row);
        });
    }

    async function submitWholesaleForm(event) {
        event.preventDefault();

        const form = document.getElementById('wholesale-form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const productId = document.getElementById('wholesale-product-id').value;
        const branchId = document.getElementById('wholesale-branch-id').value;
        const minQty = document.getElementById('wholesale-min-qty').value;
        
        // Clean price input of dots
        let priceRaw = document.getElementById('wholesale-price').value;
        const price = priceRaw.replace(/\./g, '');

        // Client-side validation: wholesale price cannot be below product buy price
        const product = productsData.find(p => p.id === productId);
        if (product && parseFloat(price) < parseFloat(product.buy_price)) {
            const formattedBuyPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(product.buy_price);
            alert(`Harga grosir tidak boleh di bawah harga beli produk (${formattedBuyPrice})`);
            return;
        }

        submitBtn.disabled = true;
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Menyimpan...';

        try {
            const response = await fetch('{{ route("wholesale-prices.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    branch_id: branchId,
                    min_qty: minQty,
                    price: price
                })
            });

            const data = await response.json();

            if (response.status === 201) {
                // Success! Get branch object info from branchesData
                const branchObj = branchesData.find(b => b.id === branchId);
                const newPriceItem = data.data;
                newPriceItem.branch = branchObj; // attach branch relation representation for UI

                // Update productsData and currentWholesalePrices
                const product = productsData.find(p => p.id === productId);
                if (product) {
                    if (!product.wholesale_prices) product.wholesale_prices = [];
                    product.wholesale_prices.push(newPriceItem);
                    currentWholesalePrices = product.wholesale_prices;
                } else {
                    currentWholesalePrices.push(newPriceItem);
                }

                renderWholesalePricesList();
                form.reset();
                
                // Show floating small notification
                showToast('Harga grosir berhasil ditambahkan!');
            } else {
                alert(data.message || 'Gagal menambahkan harga grosir');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        }
    }

    async function deleteWholesalePrice(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus harga grosir ini?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/wholesale-prices/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Remove from local memory lists
                currentWholesalePrices = currentWholesalePrices.filter(wp => wp.id !== id);
                
                const product = productsData.find(p => p.id === currentWholesaleProductId);
                if (product) {
                    product.wholesale_prices = product.wholesale_prices.filter(wp => wp.id !== id);
                }

                renderWholesalePricesList();
                showToast('Harga grosir berhasil dihapus!');
            } else {
                alert(data.message || 'Gagal menghapus harga grosir');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
        }
    }

    function showToast(message) {
        // Create dynamic toast notification matching modern aesthetics
        let toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 z-55 bg-gray-900 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2 shadow-2xl transition duration-500 transform translate-y-10 opacity-0';
        toast.innerHTML = `
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        
        // Trigger reflow & animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        }, 10);

        // Animate out & remove
        setTimeout(() => {
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 3000);
    }

    let skuCheckTimeout;

    async function checkSku(inputElement, messageElement, ignoreId = null) {
        const sku = inputElement.value.trim();
        const form = inputElement.closest('form');
        const submitBtn = form.querySelector('button[type="submit"]');

        if (sku === '') {
            messageElement.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            inputElement.classList.remove('border-red-500');
            return;
        }

        messageElement.classList.remove('hidden');
        messageElement.className = 'text-[10px] mt-1 text-gray-400 block';
        messageElement.textContent = 'Mengecek SKU...';

        try {
            let url = `/admin/products/check-sku?sku=${encodeURIComponent(sku)}`;
            if (ignoreId) {
                url += `&ignore_id=${ignoreId}`;
            }

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data.exists) {
                messageElement.className = 'text-[10px] mt-1 text-red-500 block font-bold';
                messageElement.textContent = '❌ SKU sudah digunakan!';
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                inputElement.classList.add('border-red-500');
            } else {
                messageElement.className = 'text-[10px] mt-1 text-green-400 block font-bold';
                messageElement.textContent = '✅ SKU tersedia.';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                inputElement.classList.remove('border-red-500');
            }
        } catch (error) {
            console.error('Error saat mengecek SKU:', error);
        }
    }

    // Event listener dengan debounce ringan (300ms) untuk mengurangi request ke server
    document.getElementById('create-sku').addEventListener('input', function() {
        clearTimeout(skuCheckTimeout);
        skuCheckTimeout = setTimeout(() => {
            checkSku(this, document.getElementById('create-sku-message'));
        }, 300);
    });

    document.getElementById('edit-sku').addEventListener('input', function() {
        clearTimeout(skuCheckTimeout);
        const productId = document.getElementById('edit-id').value;
        skuCheckTimeout = setTimeout(() => {
            checkSku(this, document.getElementById('edit-sku-message'), productId);
        }, 300);
    });
</script>
@endsection
