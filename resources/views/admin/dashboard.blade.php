@extends('layouts.admin')

@section('title', 'Dashboard - POS')

@section('content')
    @php
        $selectedPoId = request('po_id');
        $selectedPo = $selectedPoId ? $purchaseOrders->firstWhere('id', $selectedPoId) : $purchaseOrders->first();
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;
        $showModal = request()->has('show_modal') || request()->has('po_id');
    @endphp
    @if (session('success'))
        <div id="success-alert"
            class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2 z-10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-end gap-3 mb-6 z-10">
        <button onclick="openPermintaanPoModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition cursor-pointer relative">
            PERMINTAAN PO
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
        <button onclick="openUpcomingProductsModal()"
            class="w-full sm:w-auto justify-center px-5 py-2 rounded-full bg-[#B4F481] text-black font-bold text-xs tracking-wider hover:bg-[#a0dc72] transition flex items-center space-x-2 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>UPLOAD PRODUK BARU</span>
        </button>
    </div>

    <!-- 4 Kotak Indikator Utama -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 z-10">
        <!-- Indikator 1: Omset Penjualan -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">OMSET PENJUALAN</p>
                <div class="bg-green-950/50 p-1.5 rounded-lg text-green-400 border border-green-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white font-display">Rp {{ number_format($widgetOmset, 0, ',', '.') }}</h3>
            <p class="text-xs">
                @if($omsetTrendPercent >= 0)
                    <span class="text-green-400 font-bold">+{{ number_format($omsetTrendPercent, 1) }}%</span>
                @else
                    <span class="text-red-500 font-bold">{{ number_format($omsetTrendPercent, 1) }}%</span>
                @endif
                <span class="text-gray-500">Dari bulan lalu</span>
            </p>
        </div>

        <!-- Indikator 2: Hutang Supplier -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">HUTANG (KE SUPPLIER)</p>
                <div class="bg-yellow-950/50 p-1.5 rounded-lg text-yellow-500 border border-yellow-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-yellow-500 font-display">Rp {{ number_format($hutangSupplier, 0, ',', '.') }}</h3>
            <p class="text-xs"><span class="text-yellow-500 font-bold">Penting</span> <span class="text-gray-500">Bulan ini</span></p>
        </div>

        <!-- Indikator 3: Hutang Cabang -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">HUTANG (DARI CABANG)</p>
                <div class="bg-red-950/50 p-1.5 rounded-lg text-red-400 border border-red-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-red-400 font-display">Rp {{ number_format($hutangCabang, 0, ',', '.') }}</h3>
            <p class="text-xs"><span class="text-red-500 font-bold">Net Hutang</span> <span class="text-gray-500">Sisa saldo</span></p>
        </div>

        <!-- Indikator 4: Total SKU -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">TOTAL SKU PRODUK</p>
                <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-blue-400 font-display">{{ number_format($totalSku) }} {{ $totalSku > 1 ? 'Items' : 'Item' }}</h3>
            <p class="text-xs"><span class="text-blue-500 font-bold">{{ number_format($totalStok) }} {{ $totalStok > 1 ? 'Units' : 'Unit' }}</span> <span class="text-gray-500">Siap jual</span></p>
        </div>
    </div>

    <!-- Area Chart Grafik -->
    <div class="card p-6 rounded-xl mb-8 border-gray-700 z-10 shadow-lg shadow-black/15">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-1 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">TREN OMSET PENJUALAN BULANAN</h3>
            </div>
            <span
                class="text-[9px] text-[#B4F481] font-bold tracking-widest border border-green-900 bg-green-950/40 px-2.5 py-1 rounded-md">UPDATE
                OTOMATIS</span>
        </div>
        <!-- CANVAS FOR CHART.JS -->
        <div class="h-64 w-full relative">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Navigasi & Manajemen Sistem Bawah -->
    <div class="z-10">
        <div class="flex items-center space-x-3 mb-5">
            <div class="w-1.5 h-5 bg-white rounded-full"></div>
            <h3 class="font-bold text-sm tracking-widest font-display text-white">NAVIGASI & MANAJEMEN SISTEM</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Box 1: Produk & Stok -->
            <a href="{{ route('products.index') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-[#B4F481] font-bold mb-1 tracking-widest uppercase">Produk</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-[#B4F481] transition-colors font-display">
                        PRODUK & STOK</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Kelola katalog produk, minimal stok & import.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 2: Transaksi -->
            <a href="{{ route('sales.index') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-blue-400 font-bold mb-1 tracking-widest uppercase">Kasir</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-blue-400 transition-colors font-display">
                        TRANSAKSI</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Input transaksi penjualan, cetak struk, dan pantau
                        kasir.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 3: Data Cabang -->
            <a href="{{ route('branches.index') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-purple-400 font-bold mb-1 tracking-widest uppercase">Cabang</p>
                    <h4
                        class="font-bold text-sm mb-1 text-white group-hover:text-purple-400 transition-colors font-display">
                        DATA CABANG</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Kelola lokasi toko cabang, log aktivitas & karyawan.
                    </p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>

            <!-- Box 4: Keuangan -->
            <a href="{{ route('admin.laporan') }}"
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-yellow-400 font-bold mb-1 tracking-widest uppercase">Laporan</p>
                    <h4
                        class="font-bold text-sm mb-1 text-white group-hover:text-yellow-400 transition-colors font-display">
                        KEUANGAN</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Monitor laba rugi bulanan, setoran cash, dan
                        pengeluaran.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/2916/2916315.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </a>
        </div>
    </div>

    <!-- Chart.js Area Line Chart initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');

            // Create a gorgeous gradient fill for the area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(180, 244, 129, 0.45)');
            gradient.addColorStop(0.6, 'rgba(180, 244, 129, 0.15)');
            gradient.addColorStop(1, 'rgba(180, 244, 129, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Omset Penjualan',
                        data: {!! json_encode($chartValues) !!},
                        borderColor: '#B4F481',
                        borderWidth: 3,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#B4F481',
                        pointBorderColor: '#0B1120',
                        pointBorderWidth: 2.5,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#B4F481',
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            borderColor: '#374151',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(55, 65, 81, 0.15)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(55, 65, 81, 0.15)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    family: 'Inter',
                                    size: 11
                                },
                                callback: function (value) {
                                    if (value >= 1000) {
                                        return 'Rp ' + (value / 1000) + 'k';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
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
                    <h4 class="text-[10px] font-bold tracking-wider text-gray-500 uppercase px-2">Pilih Permintaan PO</h4>
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
                                class="w-16 h-16 rounded-2xl bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-500 mb-4 animate-bounce">
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
                            <input type="hidden" name="subtotal" id="hidden-subtotal" value="{{ $selectedPoNotes['subtotal'] ?? 0 }}">
                            <input type="hidden" name="discount" id="hidden-discount" value="{{ $selectedPoNotes['discount'] ?? 0 }}">
                            <input type="hidden" name="tax" id="hidden-tax" value="{{ $selectedPoNotes['tax'] ?? 0 }}">
                            <input type="hidden" name="grand_total" id="hidden-grand-total" value="{{ $selectedPoNotes['grand_total'] ?? 0 }}">
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
                                                        <div class="text-[10px] text-gray-505">SKU: {{ $item['sku'] ?? '' }}</div>
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
                                    @if($selectedPo->status === 'Pending')
                                        <input type="number" id="visible-discount"
                                            value="{{ $selectedPoNotes['discount'] ?? 0 }}"
                                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                    @else
                                        <input type="text" readonly
                                            value="Rp {{ number_format($selectedPoNotes['discount'] ?? 0, 0, ',', '.') }}"
                                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Pajak</label>
                                    @if($selectedPo->status === 'Pending')
                                        <input type="number" id="visible-tax"
                                            value="{{ $selectedPoNotes['tax'] ?? 0 }}"
                                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                    @else
                                        <input type="text" readonly
                                            value="Rp {{ number_format($selectedPoNotes['tax'] ?? 0, 0, ',', '.') }}"
                                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                    @endif
                                </div>
                            </div>

                            <!-- Grand Total & Payment Method -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-400">Grand Total *</label>
                                    <input type="text" readonly id="visible-grand-total"
                                        value="Rp {{ number_format($selectedPoNotes['grand_total'] ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-900 border border-gray-800 text-[#B4F481] font-bold rounded-xl p-3 cursor-not-allowed focus:outline-none">
                                </div>
                                <div class="space-y-1">
                                    <label class="block font-bold text-gray-350">Metode Pembayaran *</label>
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

    <script>
        function openPermintaanPoModal() {
            document.getElementById('permintaan-po-modal').classList.remove('hidden');
        }

        function closePermintaanPoModal() {
            window.location.href = "{{ route('admin.dashboard') }}";
        }

        function submitApprovalForm(status) {
            if (!confirm(`Apakah Anda yakin ingin menandai PO ini sebagai ${status === 'Approved' ? 'SETUJU' : 'DITOLAK'}?`)) {
                return;
            }
            document.getElementById('form-status').value = status;
            document.getElementById('approval-form').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const visibleDiscountInput = document.getElementById('visible-discount');
            const visibleTaxInput = document.getElementById('visible-tax');
            const hiddenSubtotalInput = document.getElementById('hidden-subtotal');
            const hiddenDiscountInput = document.getElementById('hidden-discount');
            const hiddenTaxInput = document.getElementById('hidden-tax');
            const hiddenGrandTotalInput = document.getElementById('hidden-grand-total');
            const visibleGrandTotalInput = document.getElementById('visible-grand-total');

            if (visibleDiscountInput && visibleTaxInput) {
                function updateTotals() {
                    const subtotal = parseFloat(hiddenSubtotalInput.value) || 0;
                    const discount = parseFloat(visibleDiscountInput.value) || 0;
                    const tax = parseFloat(visibleTaxInput.value) || 0;

                    const grandTotal = Math.max(0, subtotal - discount + tax);

                    hiddenDiscountInput.value = discount;
                    hiddenTaxInput.value = tax;
                    hiddenGrandTotalInput.value = grandTotal;

                    if (visibleGrandTotalInput) {
                        visibleGrandTotalInput.value = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
                    }
                }

                visibleDiscountInput.addEventListener('input', updateTotals);
                visibleTaxInput.addEventListener('input', updateTotals);
                visibleDiscountInput.addEventListener('change', updateTotals);
                visibleTaxInput.addEventListener('change', updateTotals);
            }
        });
    </script>

    <!-- ================= MODAL BOX: UPCOMING PRODUCTS LIST ================= -->
    <div id="upcoming-products-modal"
        class="fixed inset-0 z-40 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-5xl w-full p-0 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Close Button -->
            <button onclick="closeUpcomingProductsModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer z-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="p-6 border-b border-gray-800 bg-gray-900/40 flex justify-between items-center pr-12">
                <div>
                    <h3 class="text-base font-bold tracking-wide font-display text-white">Daftar Produk Baru (Upcoming)</h3>
                    <p class="text-[11px] text-gray-400 mt-1">Kelola daftar ide atau rencana produk baru yang akan datang</p>
                </div>
                <button onclick="openCreateUpcomingProductModal()"
                    class="bg-[#B4F481] hover:bg-[#a0dc72] text-black font-bold text-xs py-2 px-4 rounded-xl transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Rencana Produk
                </button>
            </div>

            <!-- Content Area - Table list -->
            <div class="flex-1 overflow-y-auto p-6 bg-[#0B1120]/20">
                <div class="overflow-x-auto font-sans">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 font-bold uppercase tracking-wider">
                                <th class="pb-3 px-4 w-16 text-center">Gambar</th>
                                <th class="pb-3 px-4">Nama Produk</th>
                                <th class="pb-3 px-4">Deskripsi</th>
                                <th class="pb-3 px-4">Pengunggah</th>
                                <th class="pb-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="upcoming-products-table-body" class="divide-y divide-gray-800 text-gray-300">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL BOX: CREATE UPCOMING PRODUCT ================= -->
    <div id="create-upcoming-product-modal"
        class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-850">
            <button onclick="closeCreateUpcomingProductModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Produk Baru</h3>
                <p class="text-[11px] text-gray-400 mt-1">Daftarkan rencana produk baru ke sistem</p>
            </div>
            <form onsubmit="submitCreateUpcomingProduct(event)" id="create-upcoming-form" enctype="multipart/form-data" class="space-y-4 text-xs">
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Nama Produk *</label>
                    <input type="text" name="name" required placeholder="Contoh: Susu Oat Premium"
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Detail produk baru..."
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Gambar Produk</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full bg-gray-900 border border-gray-800 text-gray-300 rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateUpcomingProductModal()"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-8 rounded-xl transition shadow-lg cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: EDIT UPCOMING PRODUCT ================= -->
    <div id="edit-upcoming-product-modal"
        class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-850">
            <button onclick="closeEditUpcomingProductModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Rencana Produk</h3>
                <p class="text-[11px] text-gray-400 mt-1">Ubah rencana detail produk baru</p>
            </div>
            <form onsubmit="submitEditUpcomingProduct(event)" id="edit-upcoming-form" enctype="multipart/form-data" class="space-y-4 text-xs">
                <input type="hidden" id="edit-upcoming-id">
                
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Nama Produk *</label>
                    <input type="text" name="name" id="edit-upcoming-name" required
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Deskripsi</label>
                    <textarea name="description" id="edit-upcoming-description" rows="3"
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Gambar Produk</label>
                    <div id="edit-upcoming-image-preview-container" class="mb-2 hidden">
                        <img id="edit-upcoming-image-preview" src="" class="w-20 h-20 rounded-lg object-cover border border-gray-800">
                        <div class="mt-1 flex items-center gap-2">
                            <input type="checkbox" name="delete_image" id="edit-upcoming-delete-image" value="1">
                            <label for="edit-upcoming-delete-image" class="text-red-400 text-[10px] cursor-pointer">Hapus gambar saat ini</label>
                        </div>
                    </div>
                    <input type="file" name="image" accept="image/*"
                        class="w-full bg-gray-900 border border-gray-800 text-gray-300 rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditUpcomingProductModal()"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-8 rounded-xl transition shadow-lg cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL BOX: DETAIL UPCOMING PRODUCT ================= -->
    <div id="detail-upcoming-product-modal"
        class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-850">
            <button onclick="closeDetailUpcomingProductModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Produk yang akan rilis</h3>
                <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap rencana produk baru</p>
            </div>
            
            <div class="space-y-4 text-xs text-gray-300">
                <!-- Large Image Preview -->
                <div class="flex justify-center bg-gray-950 p-4 rounded-xl border border-gray-800">
                    <img id="detail-upcoming-image" src="" alt="Upcoming Product Image" class="max-h-48 rounded-lg object-contain">
                </div>

                <div class="space-y-1">
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider font-display">Nama Produk</span>
                    <p id="detail-upcoming-name-view" class="text-sm font-bold text-white"></p>
                </div>

                <div class="space-y-1">
                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wider font-display">Deskripsi</span>
                    <p id="detail-upcoming-description-view" class="leading-relaxed bg-gray-900/60 p-3 rounded-xl border border-gray-800/50 whitespace-pre-line"></p>
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="button" onclick="closeDetailUpcomingProductModal()"
                    class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openUpcomingProductsModal() {
            document.getElementById('upcoming-products-modal').classList.remove('hidden');
            fetchUpcomingProducts();
        }

        function closeUpcomingProductsModal() {
            document.getElementById('upcoming-products-modal').classList.add('hidden');
        }

        function fetchUpcomingProducts() {
            const tableBody = document.getElementById('upcoming-products-table-body');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">Loading...</td>
                </tr>
            `;

            fetch('/admin/upcoming-products', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">Belum ada produk baru terdaftar.</td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(item => {
                    const imgHtml = item.image 
                        ? `<img src="${item.image}" alt="${item.name}" class="w-10 h-10 rounded-lg object-cover border border-gray-800 mx-auto shadow-md">`
                        : `<div class="w-10 h-10 rounded-lg bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-600 text-[9px] font-bold mx-auto">N/A</div>`;
                    
                    const descText = item.description 
                        ? (item.description.length > 50 ? item.description.substring(0, 50) + '...' : item.description)
                        : '-';
                    
                    const creatorName = item.creator ? item.creator.name : 'Unknown';

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-800/30 transition';
                    row.innerHTML = `
                        <td class="py-3 px-4 text-center align-middle">${imgHtml}</td>
                        <td class="py-3 px-4 font-semibold text-white align-middle">${escapeHtml(item.name)}</td>
                        <td class="py-3 px-4 text-gray-400 align-middle whitespace-pre-line">${escapeHtml(descText)}</td>
                        <td class="py-3 px-4 text-gray-400 align-middle">${escapeHtml(creatorName)}</td>
                        <td class="py-3 px-4 text-right align-middle whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailUpcomingProductModal('${item.id}')" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditUpcomingProductModal('${item.id}')" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <button onclick="deleteUpcomingProduct('${item.id}')" class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching upcoming products:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-8 text-center text-red-400">Gagal memuat data.</td>
                    </tr>
                `;
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- CREATE ACTION ---
        function openCreateUpcomingProductModal() {
            document.getElementById('create-upcoming-form').reset();
            document.getElementById('create-upcoming-product-modal').classList.remove('hidden');
        }

        function closeCreateUpcomingProductModal() {
            document.getElementById('create-upcoming-product-modal').classList.add('hidden');
        }

        function submitCreateUpcomingProduct(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('/admin/upcoming-products', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal menyimpan data.');
                }
                return response.json();
            })
            .then(data => {
                alert('Produk baru berhasil disimpan.');
                closeCreateUpcomingProductModal();
                fetchUpcomingProducts();
            })
            .catch(error => {
                console.error('Error creating upcoming product:', error);
                alert('Terjadi kesalahan saat menyimpan produk.');
            });
        }

        // --- DETAIL ACTION ---
        function openDetailUpcomingProductModal(id) {
            fetch(`/admin/upcoming-products/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(item => {
                const img = document.getElementById('detail-upcoming-image');
                if (item.image) {
                    img.src = item.image;
                    img.classList.remove('hidden');
                } else {
                    img.src = '';
                    img.classList.add('hidden');
                }

                document.getElementById('detail-upcoming-name-view').innerText = item.name;
                document.getElementById('detail-upcoming-description-view').innerText = item.description || '-';

                document.getElementById('detail-upcoming-product-modal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching detail:', error);
                alert('Gagal mengambil rincian detail produk.');
            });
        }

        function closeDetailUpcomingProductModal() {
            document.getElementById('detail-upcoming-product-modal').classList.add('hidden');
        }

        // --- EDIT ACTION ---
        function openEditUpcomingProductModal(id) {
            fetch(`/admin/upcoming-products/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(item => {
                document.getElementById('edit-upcoming-form').reset();
                document.getElementById('edit-upcoming-id').value = item.id;
                document.getElementById('edit-upcoming-name').value = item.name;
                document.getElementById('edit-upcoming-description').value = item.description || '';

                const previewContainer = document.getElementById('edit-upcoming-image-preview-container');
                const previewImg = document.getElementById('edit-upcoming-image-preview');
                document.getElementById('edit-upcoming-delete-image').checked = false;
                if (item.image) {
                    previewImg.src = item.image;
                    previewContainer.classList.remove('hidden');
                } else {
                    previewContainer.classList.add('hidden');
                }

                document.getElementById('edit-upcoming-product-modal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching data for edit:', error);
                alert('Gagal mengambil data produk.');
            });
        }

        function closeEditUpcomingProductModal() {
            document.getElementById('edit-upcoming-product-modal').classList.add('hidden');
        }

        function submitEditUpcomingProduct(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const id = document.getElementById('edit-upcoming-id').value;

            // Method override for Laravel PUT request
            formData.append('_method', 'PUT');

            fetch(`/admin/upcoming-products/${id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memperbarui data.');
                }
                return response.json();
            })
            .then(data => {
                alert('Produk baru berhasil diperbarui.');
                closeEditUpcomingProductModal();
                fetchUpcomingProducts();
            })
            .catch(error => {
                console.error('Error updating upcoming product:', error);
                alert('Terjadi kesalahan saat memperbarui produk.');
            });
        }

        // --- DELETE ACTION ---
        function deleteUpcomingProduct(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus produk baru ini dari rencana?')) {
                return;
            }

            fetch(`/admin/upcoming-products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal menghapus data.');
                }
                return response.json();
            })
            .then(data => {
                alert('Produk baru berhasil dihapus.');
                fetchUpcomingProducts();
            })
            .catch(error => {
                console.error('Error deleting upcoming product:', error);
                alert('Terjadi kesalahan saat menghapus produk.');
            });
        }
    </script>
@endsection