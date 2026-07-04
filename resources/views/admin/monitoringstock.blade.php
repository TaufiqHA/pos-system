@extends('layouts.admin')

@section('title', 'Monitoring Stok - Lucifer POS')

@section('page_title', 'Monitoring Stok')
@section('page_subtitle', 'Pantau persediaan stok produk')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-base font-bold tracking-wide font-display text-white">Status Persediaan Produk</h3>
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
                        <td class="py-4 px-4 font-semibold text-white">
                            {{ $stock->product->name ?? $stock->product_id }}
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
                                <button onclick="openEditModal({{ json_encode([
                                    'id' => $stock->id,
                                    'product_name' => $stock->product->name ?? $stock->product_id,
                                    'stock' => $stock->stock,
                                    'minimum_stock' => $stock->minimum_stock,
                                    'average_cost' => $stock->average_cost
                                ]) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500">Belum ada data stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Stok Produk</h3>
            <p class="text-[11px] text-gray-400 mt-1">Perbarui persediaan stok dan harga rata-rata</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('product-stocks.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
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
                <label for="edit-average-cost" class="block font-bold text-gray-300">Harga Rata-rata (Rp)</label>
                <input type="number" step="any" name="average_cost" id="edit-average-cost" value="{{ old('average_cost') }}" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('average_cost') border-red-500 @enderror">
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
        document.getElementById('edit-average-cost').value = stockData.average_cost;

        // Update form action dynamically
        const form = document.getElementById('edit-form');
        form.action = '/product-stocks/' + stockData.id;

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
