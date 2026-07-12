@extends('layouts.cabang')

@section('title', 'Monitoring Stok - Cabang Lucifer POS')
@section('page_title', 'MONITORING STOK')
@section('page_subtitle', 'Pantau persediaan stok produk cabang Anda')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('cabang.monitoring-stok') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
            
            <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Status --</option>
                <option value="aman" {{ request('status') === 'aman' ? 'selected' : '' }}>Aman</option>
                <option value="menipis" {{ request('status') === 'menipis' ? 'selected' : '' }}>Stok Menipis</option>
            </select>

            @if(request('search') || request('category_id') || request('status'))
                <a href="{{ route('cabang.monitoring-stok') }}" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1 cursor-pointer">
                    Reset
                </a>
            @endif
            <button type="submit" class="hidden">Search</button>
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

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4 pr-4">No</th>
                    <th class="pb-3 px-4">Produk</th>
                    <th class="pb-3 px-4">SKU</th>
                    <th class="pb-3 px-4">Kategori</th>
                    <th class="pb-3 px-4 text-right">Stok Saat Ini</th>
                    <th class="pb-3 px-4 text-right">Stok Minimum</th>
                    <th class="pb-3 px-4 text-right">Harga Rata-rata</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($stocks as $stock)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-900 border border-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                    @if($stock->product && $stock->product->image)
                                        <img src="{{ $stock->product->image }}" alt="{{ $stock->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-[9px] uppercase font-bold text-gray-600">No Img</div>
                                    @endif
                                </div>
                                <div>
                                    <span class="block font-semibold text-white">{{ $stock->product->name ?? $stock->product_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-gray-400">
                            {{ $stock->product->sku ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-gray-400">
                            {{ $stock->product->category->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2 font-mono">
                                <span class="{{ $stock->stock <= $stock->minimum_stock ? 'text-red-400 font-bold' : 'text-white' }}">
                                    {{ $stock->stock }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold {{ $stock->stock <= $stock->minimum_stock ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-green-500/10 text-[#B4F481] border border-green-500/20' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stock->stock <= $stock->minimum_stock ? 'bg-red-400' : 'bg-[#B4F481]' }}"></span>
                                    {{ $stock->stock <= $stock->minimum_stock ? 'Menipis' : 'Aman' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-gray-300">
                            {{ $stock->minimum_stock }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-gray-400">
                            Rp {{ number_format($stock->average_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick='openDetailModal({{ json_encode([
                                    "product_name" => $stock->product->name ?? $stock->product_id,
                                    "sku" => $stock->product->sku ?? "-",
                                    "category" => $stock->product->category->name ?? "-",
                                    "description" => $stock->product->description ?? "Tidak ada deskripsi",
                                    "unit" => $stock->product->unit ?? "pcs",
                                    "buy_price" => $stock->product->buy_price ?? 0,
                                    "sell_price" => $stock->product->sell_price ?? 0,
                                    "stock" => $stock->stock,
                                    "minimum_stock" => $stock->minimum_stock,
                                    "average_cost" => $stock->average_cost,
                                    "status" => $stock->stock <= $stock->minimum_stock ? "Menipis" : "Aman"
                                ]) }})' class="text-[#B4F481] hover:text-green-400 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick='openEditModal({{ json_encode([
                                    "id" => $stock->id,
                                    "product_name" => $stock->product->name ?? $stock->product_id,
                                    "stock" => $stock->stock,
                                    "minimum_stock" => $stock->minimum_stock,
                                    "average_cost" => $stock->average_cost
                                ]) }})' class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">Belum ada data stok produk di cabang ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: DETAIL STOK ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition" type="button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Stok & Produk</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap spesifikasi produk dan stok cabang</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 border-b border-gray-800 pb-3">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Nama Produk</p>
                    <p id="detail-product-name" class="text-white font-semibold mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">SKU</p>
                    <p id="detail-sku" class="text-white font-mono mt-0.5">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 border-b border-gray-800 pb-3">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Kategori</p>
                    <p id="detail-category" class="text-white mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Satuan (Unit)</p>
                    <p id="detail-unit" class="text-white mt-0.5">-</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 border-b border-gray-800 pb-3">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Stok Saat Ini</p>
                    <p id="detail-stock" class="text-white font-mono mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Stok Minimum</p>
                    <p id="detail-minimum-stock" class="text-white font-mono mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Status Stok</p>
                    <div class="mt-1">
                        <span id="detail-status" class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-semibold">
                            -
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 border-b border-gray-800 pb-3">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Harga Beli</p>
                    <p id="detail-buy-price" class="text-white font-mono mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Harga Jual</p>
                    <p id="detail-sell-price" class="text-white font-mono mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Harga Rata-rata</p>
                    <p id="detail-average-cost" class="text-[#B4F481] font-mono mt-0.5">-</p>
                </div>
            </div>

            <div>
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Deskripsi</p>
                <p id="detail-description" class="text-gray-300 mt-1 leading-relaxed">-</p>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="button" onclick="closeDetailModal()" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-5 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT STOK ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition" type="button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Stok Cabang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Perbarui persediaan stok dan harga rata-rata produk di cabang</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('cabang.monitoring-stok.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <div class="space-y-1">
                <label class="block font-bold text-gray-400">Nama Produk</label>
                <input type="text" id="edit-product-name" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none" value="{{ old('product_name') }}">
                <input type="hidden" name="product_name" id="edit-product-name-hidden" value="{{ old('product_name') }}">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-stock" class="block font-bold text-gray-300">Stok Saat Ini</label>
                    <input type="number" name="stock" id="edit-stock" value="{{ old('stock') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('stock') border-red-500 @enderror">
                    @error('stock')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="edit-minimum-stock" class="block font-bold text-gray-300">Stok Minimum</label>
                    <input type="number" name="minimum_stock" id="edit-minimum-stock" value="{{ old('minimum_stock') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('minimum_stock') border-red-500 @enderror">
                    @error('minimum_stock')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Harga Rata-rata (Rp)</label>
                <input type="text" id="edit-average-cost" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed select-none focus:outline-none" value="{{ old('average_cost') ? 'Rp ' . number_format((float)old('average_cost'), 0, ',', '.') : '' }}">
                <input type="hidden" name="average_cost" id="edit-average-cost-hidden" value="{{ old('average_cost') }}">
                @error('average_cost')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
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

<script>
    function openEditModal(stockData) {
        document.getElementById('edit-id').value = stockData.id;
        document.getElementById('edit-product-name').value = stockData.product_name;
        document.getElementById('edit-product-name-hidden').value = stockData.product_name;
        document.getElementById('edit-stock').value = stockData.stock;
        document.getElementById('edit-minimum-stock').value = stockData.minimum_stock;
        
        // Format average cost with dots and set raw value to hidden input
        const avgCost = parseFloat(stockData.average_cost) || 0;
        document.getElementById('edit-average-cost').value = 'Rp ' + Math.round(avgCost).toLocaleString('id-ID');
        document.getElementById('edit-average-cost-hidden').value = avgCost;

        // Update form action dynamically
        const form = document.getElementById('edit-form');
        form.action = '/cabang/monitoring-stok/' + stockData.id;

        // Show modal
        const modal = document.getElementById('edit-modal');
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-modal');
        modal.classList.add('hidden');
        
        // Clear validation errors and styles
        const inputs = ['edit-stock', 'edit-minimum-stock', 'edit-average-cost'];
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('border-red-500');
            }
        });
    }

    function openDetailModal(data) {
        document.getElementById('detail-product-name').innerText = data.product_name;
        document.getElementById('detail-sku').innerText = data.sku;
        document.getElementById('detail-category').innerText = data.category;
        document.getElementById('detail-unit').innerText = data.unit;
        document.getElementById('detail-stock').innerText = data.stock;
        document.getElementById('detail-minimum-stock').innerText = data.minimum_stock;
        document.getElementById('detail-description').innerText = data.description;
        
        document.getElementById('detail-buy-price').innerText = 'Rp ' + Math.round(data.buy_price).toLocaleString('id-ID');
        document.getElementById('detail-sell-price').innerText = 'Rp ' + Math.round(data.sell_price).toLocaleString('id-ID');
        document.getElementById('detail-average-cost').innerText = 'Rp ' + Math.round(data.average_cost).toLocaleString('id-ID');

        const statusBadge = document.getElementById('detail-status');
        statusBadge.innerText = data.status;
        if (data.status === 'Menipis') {
            statusBadge.className = 'inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20';
        } else {
            statusBadge.className = 'inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-green-500/10 text-[#B4F481] border border-green-500/20';
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }

    // Auto-hide success alert after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
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
