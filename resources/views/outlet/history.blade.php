@extends('layouts.outlet')

@section('title', 'Riwayat Transaksi - POS')
@section('page_title', 'RIWAYAT TRANSAKSI')
@section('page_subtitle', 'Kelola dan pantau seluruh riwayat order barang serta status pembayaran outlet')

@section('content')
    <div x-data="{
        search: '',
        statusFilter: '',
        paymentFilter: '',
        purchaseOrders: {{ $purchaseOrders->map(function($po) {
            $notes = json_decode($po->notes, true);
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'created_at' => $po->created_at->format('d M Y, H:i'),
                'creator' => $po->user->name ?? 'User',
                'status' => $po->status,
                'grand_total' => (float) ($notes['grand_total'] ?? 0),
                'payment_method' => $notes['payment_method'] ?? '-',
                'payment_status' => $po->status === 'Pending' || $po->status === 'Rejected' 
                    ? 'N/A' 
                    : ($po->sale 
                        ? (($po->sale->debt && $po->sale->debt->status === 'paid') 
                            ? 'LUNAS' 
                            : (($po->sale->debt && $po->sale->debt->status === 'partial') 
                                ? 'SEBAGIAN' 
                                : $po->sale->status)) 
                        : 'BELUM BAYAR'),
                'invoice' => $po->sale ? $po->sale->invoice : '-',
                'notes_decoded' => $notes,
                'delivery' => $po->sale && $po->sale->delivery ? [
                    'status' => $po->sale->delivery->status,
                    'driver_name' => $po->sale->delivery->driver_name,
                    'sent_at' => $po->sale->delivery->sent_at ? \Carbon\Carbon::parse($po->sale->delivery->sent_at)->format('d-m-Y H:i') : '-',
                    'received_at' => $po->sale->delivery->received_at ? \Carbon\Carbon::parse($po->sale->delivery->received_at)->format('d-m-Y H:i') : '-'
                ] : null
            ];
        })->toJson() }},
        selectedPo: null,
        selectedPoNotes: null,
        
        get filteredOrders() {
            return this.purchaseOrders.filter(po => {
                const matchesSearch = po.po_number.toLowerCase().includes(this.search.toLowerCase()) || 
                                      po.invoice.toLowerCase().includes(this.search.toLowerCase());
                const matchesStatus = this.statusFilter === '' || po.status === this.statusFilter;
                const matchesPayment = this.paymentFilter === '' || po.payment_status === this.paymentFilter;
                return matchesSearch && matchesStatus && matchesPayment;
            });
        },

        openDetail(po) {
            this.selectedPo = po;
            this.selectedPoNotes = po.notes_decoded;
            document.getElementById('detail-modal').classList.remove('hidden');
        },
        
        closeDetail() {
            document.getElementById('detail-modal').classList.add('hidden');
            this.selectedPo = null;
            this.selectedPoNotes = null;
        },

        formatCurrency(value) {
            return Math.round(value).toLocaleString('id-ID');
        }
    }" class="space-y-6">

        <!-- Filter & Search Bar -->
        <div class="card p-6 rounded-2xl shadow-xl border border-gray-800/80 bg-gray-900/20 backdrop-blur-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-1.5">Cari Transaksi</label>
                    <input type="text" x-model="search" placeholder="Cari No. PO / Invoice..." 
                        class="w-full bg-gray-900 border border-gray-800 text-white placeholder-gray-500 rounded-xl p-3 pl-10 focus:outline-none focus:border-[#B4F481] text-xs transition duration-200">
                    <div class="absolute bottom-3 left-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Status PO Filter -->
                <div>
                    <label class="block text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-1.5">Status Order</label>
                    <select x-model="statusFilter" 
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481] text-xs transition duration-200">
                        <option value="">Semua Status Order</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Completed">Completed</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-gray-400 text-[10px] font-bold tracking-wider uppercase mb-1.5">Status Pembayaran</label>
                    <select x-model="paymentFilter" 
                        class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-[#B4F481] text-xs transition duration-200">
                        <option value="">Semua Status Bayar</option>
                        <option value="LUNAS">LUNAS</option>
                        <option value="BELUM BAYAR">BELUM BAYAR</option>
                        <option value="N/A">N/A (Belum Ditagih)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Transaksi -->
        <div class="card p-6 rounded-2xl shadow-xl border border-gray-800">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-1.5 h-5 bg-[#B4F481] rounded-full"></div>
                <h3 class="font-bold text-sm tracking-wide font-display text-white">RIWAYAT TRANSAKSI OUTLET</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider bg-gray-900/10">
                            <th class="py-3.5 pl-4 pr-4">No</th>
                            <th class="py-3.5 px-4">No PO</th>
                            <th class="py-3.5 px-4">No Invoice</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Metode Bayar</th>
                            <th class="py-3.5 px-4">Status Order</th>
                            <th class="py-3.5 px-4">Status Bayar</th>
                            <th class="py-3.5 px-4">Total Belanja</th>
                            <th class="py-3.5 pl-4 pr-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60 text-xs text-gray-300">
                        <template x-for="(po, index) in filteredOrders" :key="po.id">
                            <tr class="hover:bg-gray-800/25 transition">
                                <td class="py-4 pl-4 pr-4 font-semibold text-gray-500" x-text="index + 1"></td>
                                <td class="py-4 px-4 font-semibold text-white font-mono" x-text="po.po_number"></td>
                                <td class="py-4 px-4 font-semibold text-gray-400 font-mono" x-text="po.invoice"></td>
                                <td class="py-4 px-4 text-gray-300" x-text="po.created_at"></td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                        :class="{
                                            'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': po.payment_method === 'TUNAI',
                                            'bg-purple-500/10 text-purple-400 border-purple-500/20': po.payment_method === 'TRANSFER',
                                            'bg-pink-500/10 text-pink-400 border-pink-500/20': po.payment_method === 'KREDIT',
                                            'bg-gray-500/10 text-gray-400 border-gray-500/20': po.payment_method === '-'
                                        }"
                                        x-text="po.payment_method">
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                        :class="{
                                            'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': po.status === 'Pending',
                                            'bg-blue-500/10 text-blue-400 border-blue-500/20': po.status === 'Approved',
                                            'bg-green-500/10 text-green-400 border-green-500/20': po.status === 'Completed',
                                            'bg-red-500/10 text-red-400 border-red-500/20': po.status === 'Rejected'
                                        }"
                                        x-text="po.status">
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                        :class="{
                                            'bg-green-500/10 text-green-400 border-green-500/20': po.payment_status === 'LUNAS',
                                            'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': po.payment_status === 'SEBAGIAN',
                                            'bg-red-500/10 text-red-400 border-red-500/20': po.payment_status === 'BELUM BAYAR',
                                            'bg-gray-500/10 text-gray-500 border-gray-500/20': po.payment_status === 'N/A'
                                        }"
                                        x-text="po.payment_status">
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-[#B4F481]" x-text="'Rp ' + formatCurrency(po.grand_total)"></td>
                                <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                                    <button @click="openDetail(po)" 
                                        class="text-blue-400 hover:text-blue-300 font-semibold transition px-3 py-1.5 hover:bg-blue-500/10 rounded-xl cursor-pointer">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredOrders.length === 0">
                            <td colspan="9" class="py-8 text-center text-gray-500 font-medium">Tidak ada transaksi ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= MODAL BOX: DETAIL TRANSAKSI ================= -->
        <div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto bg-gray-950">
                <!-- Close Button -->
                <button @click="closeDetail()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Header Modal -->
                <div class="mb-6">
                    <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Transaksi Order</h3>
                    <p class="text-[11px] text-gray-400 mt-1" x-text="'No PO: ' + (selectedPo ? selectedPo.po_number : '') + ' | Invoice: ' + (selectedPo ? selectedPo.invoice : '-')"></p>
                </div>

                <!-- Detail Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-xs text-gray-300">
                    <div class="space-y-2">
                        <div>
                            <span class="text-gray-500 font-bold block">TANGGAL TRANSAKSI</span>
                            <span class="text-white font-semibold" x-text="selectedPo ? selectedPo.created_at : ''"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-bold block">METODE PEMBAYARAN</span>
                            <span class="text-white font-semibold" x-text="selectedPo ? selectedPo.payment_method : ''"></span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <span class="text-gray-500 font-bold block">STATUS PEMBAYARAN</span>
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border"
                                :class="{
                                    'bg-green-500/10 text-green-400 border-green-500/20': selectedPo && selectedPo.payment_status === 'LUNAS',
                                    'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': selectedPo && selectedPo.payment_status === 'SEBAGIAN',
                                    'bg-red-500/10 text-red-400 border-red-500/20': selectedPo && selectedPo.payment_status === 'BELUM BAYAR',
                                    'bg-gray-500/10 text-gray-500 border-gray-500/20': selectedPo && selectedPo.payment_status === 'N/A'
                                }"
                                x-text="selectedPo ? selectedPo.payment_status : ''"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-bold block">CATATAN OUTLET</span>
                            <span class="text-white font-medium italic" x-text="selectedPoNotes && selectedPoNotes.user_notes ? selectedPoNotes.user_notes : '-'"></span>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-3">Item Detail</h4>
                <div class="bg-gray-900/40 rounded-xl overflow-hidden mb-6 border border-gray-800">
                    <table class="w-full text-left text-gray-300 border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-[10px] uppercase tracking-wider bg-gray-900/60">
                                <th class="py-3 px-4 font-semibold">Produk</th>
                                <th class="py-3 px-4 font-semibold text-center">Qty</th>
                                <th class="py-3 px-4 font-semibold text-right">Harga</th>
                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="selectedPoNotes && selectedPoNotes.items">
                                <template x-for="item in selectedPoNotes.items" :key="item.product_id">
                                    <tr class="border-b border-gray-800 hover:bg-gray-800/20 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-white" x-text="item.name"></div>
                                            <div class="text-[10px] text-gray-500" x-text="'SKU: ' + item.sku"></div>
                                        </td>
                                        <td class="py-3 px-4 text-center text-white" x-text="item.qty"></td>
                                        <td class="py-3 px-4 text-right text-white">
                                            <span x-text="'Rp ' + formatCurrency(item.price)"></span>
                                            <template x-if="item.is_wholesale">
                                                <span class="ml-1 inline-block bg-green-950/20 text-[#B4F481] border border-green-800/50 py-0.5 px-1.5 rounded text-[8px] font-semibold">Grosir</span>
                                            </template>
                                        </td>
                                        <td class="py-3 px-4 text-right text-white font-semibold" x-text="'Rp ' + formatCurrency(item.price * item.qty)"></td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="border-t border-gray-850 pt-4 space-y-2 text-xs text-gray-300">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-bold text-white" x-text="selectedPoNotes ? 'Rp ' + formatCurrency(selectedPoNotes.subtotal || 0) : ''"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Diskon</span>
                        <span class="font-bold text-white" x-text="selectedPoNotes ? 'Rp ' + formatCurrency(selectedPoNotes.discount || 0) : ''"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pajak</span>
                        <span class="font-bold text-white" x-text="selectedPoNotes ? 'Rp ' + formatCurrency(selectedPoNotes.tax || 0) : ''"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-800 pt-2 text-sm">
                        <span class="font-bold text-white">Grand Total</span>
                        <span class="font-extrabold text-[#B4F481]" x-text="selectedPoNotes ? 'Rp ' + formatCurrency(selectedPoNotes.grand_total || 0) : ''"></span>
                    </div>
                </div>

                <!-- Delivery Tracking -->
                <template x-if="selectedPo && selectedPo.delivery">
                    <div class="mt-6 border-t border-gray-800 pt-4">
                        <h4 class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-3">Status Pengiriman</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-300 bg-gray-900/30 p-4 rounded-xl border border-gray-800">
                            <div class="space-y-2">
                                <div>
                                    <span class="text-gray-500 font-bold block">STATUS PENGIRIMAN</span>
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-semibold"
                                        :class="{
                                            'bg-green-500/20 text-green-400': selectedPo.delivery.status === 'DITERIMA',
                                            'bg-blue-500/20 text-blue-400': selectedPo.delivery.status === 'DIKIRIM',
                                            'bg-yellow-500/20 text-yellow-400': selectedPo.delivery.status === 'PENDING'
                                        }"
                                        x-text="selectedPo.delivery.status"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 font-bold block">NAMA DRIVER</span>
                                    <span class="text-white font-semibold" x-text="selectedPo.delivery.driver_name || '-'"></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-gray-500 font-bold block">WAKTU DIKIRIM</span>
                                    <span class="text-white font-semibold" x-text="selectedPo.delivery.sent_at"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 font-bold block">WAKTU DITERIMA</span>
                                    <span class="text-white font-semibold" x-text="selectedPo.delivery.received_at"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="mt-6 flex justify-end">
                    <button @click="closeDetail()" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
