@extends('layouts.cabang')

@section('title', 'Atur Harga Cabang - Cabang Lucifer POS')
@section('page_title', 'ATUR HARGA CABANG')
@section('page_subtitle', 'Sesuaikan harga jual produk khusus untuk cabang Anda')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('product-branch-prices.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
            
            <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @if(request('search') || request('category_id'))
                <a href="{{ route('product-branch-prices.index') }}" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1 cursor-pointer">
                    Reset
                </a>
            @endif
            <button type="submit" class="hidden">Cari</button>
        </form>
    </div>

    @if (session('success'))
        <div id="success-alert" class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-xs">
            <div class="flex items-center gap-2 mb-1 font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4 pr-4">No</th>
                    <th class="pb-3 px-4">Produk</th>
                    <th class="pb-3 px-4">SKU</th>
                    <th class="pb-3 px-4">Kategori</th>
                    <th class="pb-3 px-4 text-right">Harga Beli (Pusat)</th>
                    <th class="pb-3 px-4 text-right">Harga Jual Pusat</th>
                    <th class="pb-3 px-4 text-right">Harga Jual Cabang</th>
                    <th class="pb-3 px-4 text-center">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($products as $product)
                    @php
                        $branchPrice = $product->branchPrices->first();
                    @endphp
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white">
                            {{ $product->name }}
                        </td>
                        <td class="py-4 px-4 font-mono text-gray-400">
                            {{ $product->sku ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-gray-400">
                            {{ $product->category->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-gray-400">
                            Rp {{ number_format($product->buy_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-gray-400">
                            Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold {{ $branchPrice ? 'text-[#B4F481]' : 'text-gray-300' }}">
                            Rp {{ number_format($branchPrice ? $branchPrice->sell_price : $product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($branchPrice)
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-[#B4F481]/10 text-[#B4F481] border border-[#B4F481]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#B4F481]"></span>
                                    Kustom
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    Mengikuti Pusat
                                </span>
                            @endif
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                @if($branchPrice)
                                    <button onclick="document.getElementById('edit-modal-{{ $branchPrice->id }}').classList.remove('hidden')" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                        Edit Harga
                                    </button>
                                    <form action="{{ route('product-branch-prices.delete', $branchPrice->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan harga produk ini mengikuti pusat?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
                                            Reset
                                        </button>
                                    </form>
                                @else
                                    <button onclick="document.getElementById('create-modal-{{ $product->id }}').classList.remove('hidden')" class="text-[#B4F481] hover:text-green-400 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                        Atur Harga
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- ================= MODAL BOX: ATUR HARGA (CREATE) ================= -->
                    @if(!$branchPrice)
                        <div id="create-modal-{{ $product->id }}" class="fixed inset-0 z-50 {{ $errors->any() && old('product_id') === $product->id ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                            <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
                                <button onclick="document.getElementById('create-modal-{{ $product->id }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white transition" type="button">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div class="mb-6">
                                    <h3 class="text-base font-bold tracking-wide font-display text-white">Atur Harga Khusus Cabang</h3>
                                    <p class="text-[11px] text-gray-400 mt-1">Tetapkan harga jual kustom untuk produk ini di cabang Anda</p>
                                </div>
                                <form action="{{ route('product-branch-prices.create') }}" method="POST" class="space-y-4 text-xs">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="branch_id" value="{{ $branch->id ?? '' }}">

                                    <div class="space-y-1">
                                        <label class="block font-bold text-gray-400">Nama Produk</label>
                                        <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="{{ $product->name }}">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block font-bold text-gray-400">Harga Beli Pusat</label>
                                            <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="Rp {{ number_format($product->buy_price, 0, ',', '.') }}">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block font-bold text-gray-400">Harga Jual Pusat</label>
                                            <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="Rp {{ number_format($product->sell_price, 0, ',', '.') }}">
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label for="sell_price_create_{{ $product->id }}" class="block font-bold text-gray-300">Harga Jual Cabang Baru (Rp)</label>
                                        <input type="text" name="sell_price" id="sell_price_create_{{ $product->id }}" value="{{ old('product_id') === $product->id ? old('sell_price') : $product->sell_price }}" placeholder="Masukkan harga kustom..." oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                                        <p class="text-[10px] text-gray-500 mt-1">Catatan: Harga jual tidak boleh di bawah harga beli pusat.</p>
                                    </div>

                                    <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                                        <button type="button" onclick="document.getElementById('create-modal-{{ $product->id }}').classList.add('hidden')" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                                            Batal
                                        </button>
                                        <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                                            Simpan Harga
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- ================= MODAL BOX: EDIT HARGA (UPDATE) ================= -->
                    @if($branchPrice)
                        <div id="edit-modal-{{ $branchPrice->id }}" class="fixed inset-0 z-50 {{ $errors->any() && old('id') === $branchPrice->id ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                            <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
                                <button onclick="document.getElementById('edit-modal-{{ $branchPrice->id }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white transition" type="button">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div class="mb-6">
                                    <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Harga Khusus Cabang</h3>
                                    <p class="text-[11px] text-gray-400 mt-1">Ubah harga jual kustom produk Anda di cabang ini</p>
                                </div>
                                <form action="{{ route('product-branch-prices.update', $branchPrice->id) }}" method="POST" class="space-y-4 text-xs">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $branchPrice->id }}">

                                    <div class="space-y-1">
                                        <label class="block font-bold text-gray-400">Nama Produk</label>
                                        <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="{{ $product->name }}">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="block font-bold text-gray-400">Harga Beli Pusat</label>
                                            <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="Rp {{ number_format($product->buy_price, 0, ',', '.') }}">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block font-bold text-gray-400">Harga Jual Pusat</label>
                                            <input type="text" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="Rp {{ number_format($product->sell_price, 0, ',', '.') }}">
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label for="sell_price_edit_{{ $branchPrice->id }}" class="block font-bold text-gray-300">Harga Jual Cabang Baru (Rp)</label>
                                        <input type="text" name="sell_price" id="sell_price_edit_{{ $branchPrice->id }}" value="{{ old('id') === $branchPrice->id ? old('sell_price') : $branchPrice->sell_price }}" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                                        <p class="text-[10px] text-gray-500 mt-1">Catatan: Harga jual tidak boleh di bawah harga beli pusat.</p>
                                    </div>

                                    <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                                        <button type="button" onclick="document.getElementById('edit-modal-{{ $branchPrice->id }}').classList.add('hidden')" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                                            Batal
                                        </button>
                                        <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-500">Tidak ditemukan data produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
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
        // Auto-format any pre-filled/validation-failed price inputs on load
        const priceInputsOnLoad = document.querySelectorAll('input[name="sell_price"]');
        priceInputsOnLoad.forEach(input => {
            if (input.value) {
                formatRupiah(input);
            }
        });

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const priceInputs = form.querySelectorAll('input[name="sell_price"]');
                priceInputs.forEach(input => {
                    if (input.value) {
                        // Hapus semua titik agar menjadi angka murni untuk dikirim ke backend
                        input.value = input.value.replace(/\./g, '');
                    }
                });
            });
        });

        // Auto-hide success alert after 3 seconds
        const alert = document.getElementById('success-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }
    });
</script>
@endsection
