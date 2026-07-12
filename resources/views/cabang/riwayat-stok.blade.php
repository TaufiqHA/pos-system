@extends('layouts.cabang')

@section('title', 'Monitoring Riwayat Stok - Lucifer POS')
@section('page_title', 'RIWAYAT STOK')
@section('page_subtitle', 'Pantau dan telusuri riwayat pergerakan keluar-masuk stok barang')

@section('content')
    <div class="card p-6 rounded-2xl shadow-xl z-10">
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Log Pergerakan Stok</h3>
            <p class="text-[11px] text-gray-400 mt-1">Daftar transaksi mutasi masuk dan keluar barang di cabang Anda</p>
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
                        <th class="pb-3 px-4">Tanggal</th>
                        <th class="pb-3 px-4">Produk</th>
                        <th class="pb-3 px-4">Tipe</th>
                        <th class="pb-3 px-4 text-right">Jumlah (Qty)</th>
                        <th class="pb-3 px-4 text-right">Stok Awal</th>
                        <th class="pb-3 px-4 text-right">Stok Akhir</th>
                        <th class="pb-3 px-4">Referensi</th>
                        <th class="pb-3 pl-4 pr-4">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                    @forelse($histories as $history)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                            <td class="py-4 px-4 text-gray-400 font-medium">
                                {{ $history->created_at ? $history->created_at->format('d-m-Y H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-900 border border-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                        @if($history->product && $history->product->image)
                                            <img src="{{ $history->product->image }}" alt="{{ $history->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="text-[9px] uppercase font-bold text-gray-600">No Img</div>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block font-semibold text-white">{{ $history->product->name ?? 'Produk Tidak Ditemukan' }}</span>
                                        <div class="text-[10px] text-gray-500 font-mono">SKU: {{ $history->product->sku ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold 
                                                @if(strtoupper($history->type) === 'IN') bg-green-500/10 text-[#B4F481] border border-green-500/20
                                                @elseif(strtoupper($history->type) === 'OUT') bg-red-500/10 text-red-400 border border-red-500/20
                                                @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                                    @if(strtoupper($history->type) === 'IN') bg-[#B4F481]
                                                    @elseif(strtoupper($history->type) === 'OUT') bg-red-400
                                                    @else bg-blue-400 @endif"></span>
                                    {{ strtoupper($history->type) }}
                                </span>
                            </td>
                            <td
                                class="py-4 px-4 text-right font-mono font-bold @if(strtoupper($history->type) === 'IN') text-[#B4F481] @elseif(strtoupper($history->type) === 'OUT') text-red-400 @else text-blue-400 @endif">
                                {{ strtoupper($history->type) === 'IN' ? '+' : (strtoupper($history->type) === 'OUT' ? '-' : '') }}{{ $history->qty }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-gray-400">
                                {{ $history->previous_stock }}
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-white">
                                {{ $history->new_stock }}
                            </td>
                            <td class="py-4 px-4 font-medium text-gray-400">
                                @if($history->reference)
                                    @if(class_basename($history->reference_type) === 'Sales')
                                        <span class="text-blue-400 font-bold font-mono">Penjualan
                                            #{{ $history->reference->invoice ?? '-' }}</span>
                                    @elseif(class_basename($history->reference_type) === 'Purchases')
                                        <span class="text-green-400 font-bold font-mono">Pembelian
                                            #{{ $history->reference->invoice ?? '-' }}</span>
                                    @else
                                        <span class="text-gray-400 font-mono">{{ class_basename($history->reference_type) }}
                                            #{{ $history->reference_id }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="py-4 pl-4 pr-4 text-gray-300">
                                {{ $history->user->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-500 font-medium">Belum ada riwayat pergerakan
                                stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Auto-hide success alert after 3 seconds
        document.addEventListener('DOMContentLoaded', function () {
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