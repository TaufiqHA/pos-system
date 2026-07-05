@extends('layouts.cabang')

@section('title', 'Dashboard Cabang - POS')
@section('page_title', 'DASHBOARD CABANG')
@section('page_subtitle', 'Sistem Informasi & POS Cabang Lucifer')

@section('content')
    @if (session('success'))
        <div id="success-alert"
            class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-end gap-3 mb-6 z-10">
        <button onclick="openPoModal()"
            class="w-full sm:w-auto flex items-center justify-center px-5 py-2 rounded-full border border-green-400 text-green-400 font-bold text-xs tracking-wider hover:bg-green-400 hover:text-black transition cursor-pointer">
            PO KE PUSAT
        </button>
        <button
            class="w-full sm:w-auto justify-center px-5 py-2 rounded-full bg-[#B4F481] text-black font-bold text-xs tracking-wider hover:bg-[#a0dc72] transition flex items-center space-x-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>TRANSAKSI BARU</span>
        </button>
    </div>

    <!-- 4 Kotak Indikator Utama Cabang -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 z-10">
        <!-- Indikator 1: Omset Penjualan Hari Ini -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">OMSET HARI INI</p>
                <div class="bg-green-950/50 p-1.5 rounded-lg text-green-400 border border-green-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-white font-display">Rp 450.000</h3>
            <p class="text-xs"><span class="text-green-400 font-bold">+8.3%</span> <span class="text-gray-500">Dari
                    kemarin</span></p>
        </div>

        <!-- Indikator 2: PO Pending -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">PO KE PUSAT (PENDING)</p>
                <div class="bg-yellow-950/50 p-1.5 rounded-lg text-yellow-500 border border-yellow-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-yellow-500 font-display">2 PO</h3>
            <p class="text-xs"><span class="text-yellow-500 font-bold">Dalam Proses</span> <span
                    class="text-gray-500">Konfirmasi pusat</span></p>
        </div>

        <!-- Indikator 3: Stok Menipis -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">STOK MENIPIS</p>
                <div class="bg-red-950/50 p-1.5 rounded-lg text-red-400 border border-red-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-red-400 font-display">8 Item</h3>
            <p class="text-xs"><span class="text-red-500 font-bold">Segera Re-stock</span> <span class="text-gray-500">Perlu
                    pesan ke pusat</span></p>
        </div>

        <!-- Indikator 4: Transaksi Hari Ini -->
        <div class="card p-5 rounded-xl hover:border-gray-500 transition shadow-lg shadow-black/20">
            <div class="flex justify-between items-start mb-3">
                <p class="text-[10px] text-gray-400 font-bold tracking-wider">TRANSAKSI HARI INI</p>
                <div class="bg-blue-950/50 p-1.5 rounded-lg text-blue-400 border border-blue-800/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2 text-blue-400 font-display">12 Transaksi</h3>
            <p class="text-xs"><span class="text-blue-500 font-bold">Aktif</span> <span class="text-gray-500">Di kasir
                    utama</span></p>
        </div>
    </div>

    <!-- Area Chart Grafik Tren Penjualan Cabang -->
    <div class="card p-6 rounded-xl mb-8 border-gray-700 z-10 shadow-lg shadow-black/15">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-1 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">TREN OMSET PENJUALAN CABANG BULANAN</h3>
            </div>
            <span
                class="text-[9px] text-[#B4F481] font-bold tracking-widest border border-green-900 bg-green-950/40 px-2.5 py-1 rounded-md">LIVE
                UPDATE</span>
        </div>
        <!-- CANVAS FOR CHART.JS -->
        <div class="h-64 w-full relative">
            <canvas id="salesChartCabang"></canvas>
        </div>
    </div>

    <!-- Navigasi & Manajemen Sistem Bawah -->
    <div class="z-10">
        <div class="flex items-center space-x-3 mb-5">
            <div class="w-1.5 h-5 bg-white rounded-full"></div>
            <h3 class="font-bold text-sm tracking-widest font-display text-white">MODUL UTAMA CABANG</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Box 1: Produk & Stok -->
            <div
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-[#B4F481] font-bold mb-1 tracking-widest uppercase">Katalog</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-[#B4F481] transition-colors font-display">
                        PRODUK & STOK</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Monitoring stok outlet, cek riwayat, dan update harga
                        jual.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3144/3144456.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </div>

            <!-- Box 2: Transaksi -->
            <div
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-blue-400 font-bold mb-1 tracking-widest uppercase">Kasir</p>
                    <h4 class="font-bold text-sm mb-1 text-white group-hover:text-blue-400 transition-colors font-display">
                        TRANSAKSI</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Entri penjualan harian, pesan PO ke pusat, kelola
                        daftar PO.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </div>

            <!-- Box 3: Data Cabang -->
            <div
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-purple-400 font-bold mb-1 tracking-widest uppercase">Outlet</p>
                    <h4
                        class="font-bold text-sm mb-1 text-white group-hover:text-purple-400 transition-colors font-display">
                        DATA CABANG</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Informasi profil outlet cabang dan daftar karyawan
                        internal.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </div>

            <!-- Box 4: Keuangan -->
            <div
                class="card p-5 rounded-xl flex items-center justify-between hover:bg-gray-800/80 cursor-pointer transition border border-gray-750 shadow-md group">
                <div class="pr-2">
                    <p class="text-[9px] text-yellow-400 font-bold mb-1 tracking-widest uppercase">Laporan</p>
                    <h4
                        class="font-bold text-sm mb-1 text-white group-hover:text-yellow-400 transition-colors font-display">
                        KEUANGAN</h4>
                    <p class="text-[10px] text-gray-400 leading-snug">Laporan keuangan outlet harian, laba bersih, dan sisa
                        hutang.</p>
                </div>
                <div
                    class="w-12 h-12 bg-white/5 rounded-xl border border-gray-700/80 flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <img src="https://cdn-icons-png.flaticon.com/512/2916/2916315.png" class="w-6 h-6 invert" alt="Icon">
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Area Line Chart initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChartCabang').getContext('2d');

            // Create a gorgeous gradient fill for the area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, 'rgba(180, 244, 129, 0.45)');
            gradient.addColorStop(0.6, 'rgba(180, 244, 129, 0.15)');
            gradient.addColorStop(1, 'rgba(180, 244, 129, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Omset Cabang',
                        data: [15000, 22000, 18000, 35000, 28000, 41000, 45000],
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

    <!-- ================= MODAL BOX: PO KE PUSAT ================= -->
    <div id="po-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="card max-w-4xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
            <!-- Tombol Close (X) -->
            <button onclick="closePoModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header Modal -->
            <div class="mb-6">
                <h3 class="text-base font-bold tracking-wide font-display text-white">Buat PO ke Pusat</h3>
                <p class="text-[11px] text-gray-400 mt-1">Formulir pengajuan Purchase Order barang ke gudang pusat</p>
            </div>

            <!-- Form Input UI -->
            <form id="po-form" action="{{ route('purchase-orders.store') }}" method="POST"
                onsubmit="return validatePoForm(event)" class="space-y-6 text-xs">
                @csrf
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? '' }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id ?? '' }}">
                <input type="hidden" name="status" value="Pending">
                <div id="po-hidden-items-container"></div>

                <!-- Grid 2 Kolom Atas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Pusat (Readonly) -->
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Pusat *</label>
                        <input type="text" readonly value="{{ auth()->user()->parent->name ?? 'Pusat' }}"
                            class="w-full bg-gray-900 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                    </div>

                    <!-- Tanggal Transaksi -->
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Tanggal Transaksi *</label>
                        <div class="relative">
                            <input type="text" readonly value="{{ now()->format('d/m/Y, h:i A') }}"
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

                <!-- Section Item PO -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase">ITEM PO</h4>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <select id="po-product-select"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-sku="{{ $product->sku }}"
                                        data-price="{{ $product->buy_price }}" data-name="{{ $product->name }}">
                                        {{ $product->name }} (SKU: {{ $product->sku }}) - Rp
                                        {{ number_format($product->buy_price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-24">
                            <input type="number" id="po-qty-input" placeholder="Qty" min="1"
                                class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                        </div>
                        <div class="w-full sm:w-36">
                            <input type="text" id="po-price-input" placeholder="Harga" readonly
                                class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        </div>
                        <button type="button" onclick="addPoItem()"
                            class="bg-[#B4F481] hover:bg-green-400 text-black font-bold p-3 rounded-xl transition w-full sm:w-12 flex items-center justify-center cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Table Item PO -->
                <div class="bg-gray-900/40 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                                <th class="py-3 px-4 font-semibold">Produk</th>
                                <th class="py-3 px-4 font-semibold">Qty</th>
                                <th class="py-3 px-4 font-semibold">Harga</th>
                                <th class="py-3 px-4 font-semibold">Subtotal</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="po-items-table-body">
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500 font-medium">Belum ada item
                                    ditambahkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Kolom 1 (Subtotal, Diskon, Pajak) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Subtotal *</label>
                        <input type="text" id="po-subtotal-display" value="0" readonly
                            class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                        <input type="hidden" id="po-subtotal" name="subtotal" value="0">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Diskon</label>
                        <input type="number" id="po-discount-input" name="discount" value="0" min="0"
                            oninput="calculateGrandTotal()"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-gray-300">Pajak</label>
                        <input type="number" id="po-tax-input" name="tax" value="0" min="0" oninput="calculateGrandTotal()"
                            class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]">
                    </div>
                </div>

                <!-- Ringkasan Kolom 2 (Grand Total) -->
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Grand Total *</label>
                    <input type="text" id="po-grand-total-display" value="0" readonly
                        class="w-full bg-gray-950 border border-gray-800 text-gray-400 rounded-xl p-3 cursor-not-allowed focus:outline-none">
                    <input type="hidden" id="po-grand-total" name="grand_total" value="0">
                </div>
                <input type="hidden" id="po-payment-method" name="payment_method" value="KREDIT">

                <!-- Notes / Catatan -->
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Catatan / Notes</label>
                    <textarea id="po-notes-input" name="user_notes" placeholder="Masukkan catatan tambahan jika ada..."
                        rows="3"
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481]"></textarea>
                </div>

                <!-- Tombol Action -->
                <div
                    class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-850">
                    <button type="button" onclick="closePoModal()"
                        class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-6 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-po"
                        class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-8 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                        Kirim PO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka dan menutup Modal PO
        let poItems = [];

        function openPoModal() {
            document.getElementById('po-modal').classList.remove('hidden');
        }

        function closePoModal() {
            document.getElementById('po-modal').classList.add('hidden');
        }

        function formatCurrency(value) {
            return Math.round(value).toLocaleString('id-ID');
        }

        function parseCurrency(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        // Auto prefill price when product changes
        document.getElementById('po-product-select').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const priceInput = document.getElementById('po-price-input');
            if (selectedOption && selectedOption.value) {
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                priceInput.value = formatCurrency(price);
            } else {
                priceInput.value = '';
            }
        });

        function addPoItem() {
            const select = document.getElementById('po-product-select');
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                alert('Silakan pilih produk terlebih dahulu.');
                return;
            }

            const qtyInput = document.getElementById('po-qty-input');
            const qty = parseInt(qtyInput.value);
            if (!qty || qty <= 0) {
                alert('Silakan masukkan jumlah (Qty) yang valid.');
                return;
            }

            const priceInput = document.getElementById('po-price-input');
            const price = parseCurrency(priceInput.value);
            if (isNaN(price) || price < 0) {
                alert('Silakan masukkan harga yang valid.');
                return;
            }

            const productId = selectedOption.value;
            const name = selectedOption.getAttribute('data-name');
            const sku = selectedOption.getAttribute('data-sku');

            // Check if product already added
            const existingIndex = poItems.findIndex(item => item.product_id === productId);
            if (existingIndex > -1) {
                poItems[existingIndex].qty += qty;
            } else {
                poItems.push({
                    product_id: productId,
                    name: name,
                    sku: sku,
                    qty: qty,
                    price: price
                });
            }

            // Reset inputs
            select.value = '';
            qtyInput.value = '';
            priceInput.value = '';

            updatePoTable();
        }

        function removePoItem(index) {
            poItems.splice(index, 1);
            updatePoTable();
        }

        function updatePoTable() {
            const tbody = document.getElementById('po-items-table-body');
            tbody.innerHTML = '';

            const hiddenContainer = document.getElementById('po-hidden-items-container');
            hiddenContainer.innerHTML = '';

            if (poItems.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-gray-500 font-medium">Belum ada item ditambahkan</td></tr>`;
                document.getElementById('po-subtotal').value = 0;
                document.getElementById('po-subtotal-display').value = '0';
                document.getElementById('po-grand-total').value = 0;
                document.getElementById('po-grand-total-display').value = '0';
                return;
            }

            let subtotal = 0;
            poItems.forEach((item, index) => {
                const itemSubtotal = item.qty * item.price;
                subtotal += itemSubtotal;

                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-800';
                tr.innerHTML = `
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-white">${item.name}</div>
                                        <div class="text-[10px] text-gray-400">SKU: ${item.sku}</div>
                                    </td>
                                    <td class="py-3 px-4 text-white">${item.qty}</td>
                                    <td class="py-3 px-4 text-white">Rp ${item.price.toLocaleString('id-ID')}</td>
                                    <td class="py-3 px-4 text-white">Rp ${itemSubtotal.toLocaleString('id-ID')}</td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" onclick="removePoItem(${index})" class="text-red-400 hover:text-red-300 font-bold transition">Hapus</button>
                                    </td>
                                `;
                tbody.appendChild(tr);

                // Append hidden inputs for standard form submission
                hiddenContainer.innerHTML += `
                                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                    <input type="hidden" name="items[${index}][name]" value="${item.name}">
                                    <input type="hidden" name="items[${index}][sku]" value="${item.sku}">
                                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                                `;
            });

            document.getElementById('po-subtotal').value = subtotal;
            document.getElementById('po-subtotal-display').value = formatCurrency(subtotal);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const subtotal = parseFloat(document.getElementById('po-subtotal').value) || 0;
            const discount = parseFloat(document.getElementById('po-discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('po-tax-input').value) || 0;

            const grandTotal = Math.max(0, subtotal - discount + tax);
            document.getElementById('po-grand-total').value = grandTotal;
            document.getElementById('po-grand-total-display').value = formatCurrency(grandTotal);
        }

        function validatePoForm(event) {
            if (poItems.length === 0) {
                alert('Silakan tambahkan minimal 1 item PO.');
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endsection