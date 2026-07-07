@extends('layouts.admin')

@section('title', 'Laporan Cabang - Lucifer POS')

@section('page_title', 'LAPORAN CABANG')
@section('page_subtitle', 'Monitoring data, stok, dan transaksi cabang')

@section('content')
    <div class="card rounded-2xl overflow-hidden shadow-xl mb-8 border border-gray-800">
        <!-- Tabs Header -->
        <div class="grid grid-cols-3 border-b border-gray-800 bg-gray-900/40">
            <a href="{{ route('admin.laporan-cabang', ['tab' => 'manajemen'] + request()->except('tab')) }}" 
               class="py-4 text-center text-xs font-bold uppercase tracking-wider transition-all cursor-pointer {{ $tab === 'manajemen' ? 'border-b-2 border-[#B4F481] text-[#B4F481] bg-gray-800/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/10' }}">
                Manajemen Data
            </a>
            <a href="{{ route('admin.laporan-cabang', ['tab' => 'stok'] + request()->except('tab')) }}" 
               class="py-4 text-center text-xs font-bold uppercase tracking-wider transition-all cursor-pointer {{ $tab === 'stok' ? 'border-b-2 border-[#B4F481] text-[#B4F481] bg-gray-800/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/10' }}">
                Stok Cabang
            </a>
            <a href="{{ route('admin.laporan-cabang', ['tab' => 'transaksi'] + request()->except('tab')) }}" 
               class="py-4 text-center text-xs font-bold uppercase tracking-wider transition-all cursor-pointer {{ $tab === 'transaksi' ? 'border-b-2 border-[#B4F481] text-[#B4F481] bg-gray-800/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/10' }}">
                Transaksi Cabang
            </a>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- 1. TAB MANAJEMEN DATA -->
            @if($tab === 'manajemen')
                <div class="space-y-4">
                    @forelse($regions as $region)
                        <div x-data="{ open: false }" class="bg-gray-900/30 border border-gray-800 rounded-xl overflow-hidden transition-all duration-300">
                            <button @click="open = !open" class="w-full flex justify-between items-center p-5 text-left cursor-pointer hover:bg-gray-800/25 transition-all focus:outline-none">
                                <div class="flex items-center space-x-3">
                                    <div class="w-1.5 h-6 bg-[#B4F481] rounded-full"></div>
                                    <span class="font-bold text-sm tracking-wide text-white uppercase">{{ $region->name }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-[10px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                                        {{ $region->branches->count() }} Cabang
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0 transform scale-y-95 origin-top" 
                                 x-transition:enter-end="opacity-100 transform scale-y-100 origin-top"
                                 class="border-t border-gray-800 bg-gray-950/20 px-6 py-4" 
                                 style="display: none;">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                                        <thead>
                                            <tr class="border-b border-gray-800 text-gray-400 font-bold uppercase tracking-wider">
                                                <th class="pb-3 pl-2">Nama Cabang</th>
                                                <th class="pb-3 px-4">Alamat</th>
                                                <th class="pb-3 text-right pr-2">Jumlah Outlet</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-800 text-gray-300">
                                            @forelse($region->branches as $branch)
                                                <tr class="hover:bg-gray-800/30 transition">
                                                    <td class="py-3 pl-2 font-bold text-white uppercase">{{ $branch->name }}</td>
                                                    <td class="py-3 px-4 text-gray-400">{{ $branch->address ?? '-' }}</td>
                                                    <td class="py-3 text-right font-bold text-[#B4F481] pr-2">{{ $branch->outlets_count }} Outlet</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-4 text-center text-gray-500">Tidak ada cabang terdaftar di wilayah ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-500">Belum ada data wilayah.</div>
                    @endforelse
                </div>
            @endif

            <!-- 2. TAB STOK CABANG -->
            @if($tab === 'stok')
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin.laporan-cabang') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <input type="hidden" name="tab" value="stok">
                    
                    <!-- Wilayah Filter -->
                    <div class="relative">
                        <select name="wilayah_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Wilayah</option>
                            @foreach($wilayahs as $w)
                                <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <!-- Cabang Filter -->
                    <div class="relative">
                        <select name="branch_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <!-- Kategori Filter -->
                    <div class="relative">
                        <select name="category_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                                <th class="pb-3 pl-4">PRODUK</th>
                                <th class="pb-3 px-4">SKU</th>
                                <th class="pb-3 px-4">KATEGORI</th>
                                <th class="pb-3 px-4">CABANG</th>
                                <th class="pb-3 px-4 text-right pr-4">STOK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                            @forelse($stocks as $stock)
                                <tr class="hover:bg-gray-800/30 transition">
                                    <td class="py-4 pl-4 font-semibold text-white uppercase">{{ $stock->product?->name ?? '-' }}</td>
                                    <td class="py-4 px-4 font-mono font-semibold text-gray-400">{{ $stock->product?->sku ?? '-' }}</td>
                                    <td class="py-4 px-4">
                                        <span class="bg-indigo-500/10 text-indigo-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-indigo-500/20">
                                            {{ $stock->product?->category?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-400">{{ $stock->branch?->name ?? '-' }}</td>
                                    <td class="py-4 px-4 text-right pr-4 font-bold text-white">
                                        <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold {{ $stock->stock <= $stock->minimum_stock ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-green-500/10 text-[#B4F481] border border-green-500/20' }}">
                                            {{ $stock->stock }} {{ $stock->product?->unit ?? 'pcs' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-500 font-medium tracking-wide">TIDAK ADA DATA STOK DI CABANG INI.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- 3. TAB TRANSAKSI CABANG -->
            @if($tab === 'transaksi')
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin.laporan-cabang') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <input type="hidden" name="tab" value="transaksi">
                    
                    <!-- Wilayah Filter -->
                    <div class="relative">
                        <select name="wilayah_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Wilayah</option>
                            @foreach($wilayahs as $w)
                                <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <!-- Cabang Filter -->
                    <div class="relative">
                        <select name="branch_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <!-- Kategori Filter -->
                    <div class="relative">
                        <select name="category_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl py-3 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer appearance-none">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                                <th class="pb-3 pl-4">No</th>
                                <th class="pb-3 px-4">Invoice</th>
                                <th class="pb-3 px-4">Tanggal</th>
                                <th class="pb-3 px-4">Cabang</th>
                                <th class="pb-3 px-4">Outlet</th>
                                <th class="pb-3 px-4 text-right">Total</th>
                                <th class="pb-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                            @forelse($transactions as $sale)
                                <tr class="hover:bg-gray-800/30 transition">
                                    <td class="py-4 pl-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="py-4 px-4 font-semibold text-white font-mono">{{ $sale->invoice }}</td>
                                    <td class="py-4 px-4 text-gray-400">
                                        {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y H:i') }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-300 font-semibold uppercase">{{ $sale->branch?->name ?? '-' }}</td>
                                    <td class="py-4 px-4 text-indigo-400 font-semibold uppercase">{{ $sale->outlet?->name ?? '-' }}</td>
                                    <td class="py-4 px-4 text-right font-bold text-white">
                                        Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                                @if($sale->status === 'completed' || $sale->status === 'LUNAS') bg-green-500/10 text-green-400 border border-green-500/20
                                                @elseif($sale->status === 'pending' || $sale->status === 'BELUM BAYAR') bg-yellow-500/10 text-yellow-400 border border-yellow-500/20
                                                @else bg-red-500/10 text-red-400 border border-red-500/20 @endif">
                                            {{ strtoupper($sale->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-500 font-medium tracking-wide">TIDAK ADA DATA TRANSAKSI DI CABANG INI.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
