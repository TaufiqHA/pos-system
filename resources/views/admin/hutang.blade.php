@extends('layouts.admin')

@section('title', 'Kelola Hutang & Piutang - Lucifer POS')

@section('page_title', 'Hutang & Piutang')
@section('page_subtitle', 'Monitoring hutang pembelian supplier dan piutang penjualan')

@section('content')
@php
    $totalDebt = $debts->sum('total_amount');
    $totalPaid = $debts->sum('paid_amount');
    $totalRemaining = $debts->sum('remaining_amount');
    $unpaidCount = $debts->where('status', '!=', 'paid')->count();
@endphp

<!-- Statistik Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="card p-5 rounded-2xl flex items-center gap-4 relative overflow-hidden shadow-lg border border-gray-800">
        <div class="p-3 bg-red-500/10 text-red-400 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400">Total Hutang</p>
            <p class="text-lg font-bold text-white mt-1">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card p-5 rounded-2xl flex items-center gap-4 relative overflow-hidden shadow-lg border border-gray-800">
        <div class="p-3 bg-green-500/10 text-green-400 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400">Total Terbayar</p>
            <p class="text-lg font-bold text-white mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card p-5 rounded-2xl flex items-center gap-4 relative overflow-hidden shadow-lg border border-gray-800">
        <div class="p-3 bg-yellow-500/10 text-yellow-400 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400">Sisa Hutang</p>
            <p class="text-lg font-bold text-white mt-1">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card p-5 rounded-2xl flex items-center gap-4 relative overflow-hidden shadow-lg border border-gray-800">
        <div class="p-3 bg-[#B4F481]/10 text-[#B4F481] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400">Hutang Aktif</p>
            <p class="text-lg font-bold text-white mt-1">{{ $unpaidCount }} Transaksi</p>
        </div>
    </div>
</div>

<!-- Card Utama -->
<div class="card p-6 rounded-2xl shadow-xl border border-gray-800">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Filter Status -->
            <select id="filter-status" onchange="filterDebts()" class="bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 text-xs focus:outline-none focus:border-green-400">
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Dibayar (Unpaid)</option>
                <option value="partial">Dibayar Sebagian (Partial)</option>
                <option value="paid">Lunas (Paid)</option>
            </select>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alert-container" class="hidden mb-4 p-4 rounded-xl text-xs flex items-center gap-2"></div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4 pr-4">No</th>
                    <th class="pb-3 px-4">Kreditor (Pemberi)</th>
                    <th class="pb-3 px-4">Debitur (Penerima)</th>
                    <th class="pb-3 px-4">Sumber / Invoice</th>
                    <th class="pb-3 px-4">Total</th>
                    <th class="pb-3 px-4">Terbayar</th>
                    <th class="pb-3 px-4">Sisa</th>
                    <th class="pb-3 px-4">Jatuh Tempo</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="debts-table-body" class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($debts as $debt)
                    <tr class="hover:bg-gray-800/30 transition debt-row" 
                        data-status="{{ $debt->status }}" 
                        data-creditor="{{ $debt->creditor_type === 'supplier' ? 'supplier-'.$debt->supplier_id : 'branch-'.$debt->creditor_branch_id }}">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white">
                            @if($debt->creditor_type === 'supplier')
                                <span>{{ $debt->supplier->name ?? 'Supplier' }}</span>
                                <span class="block text-[10px] text-gray-500 font-normal">Supplier</span>
                            @else
                                <span>{{ $debt->creditorBranch->name ?? 'Cabang' }}</span>
                                <span class="block text-[10px] text-gray-500 font-normal">Cabang</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-gray-300">
                            @if($debt->debtor_type === 'branch')
                                <span>{{ $debt->debtorBranch->name ?? 'Cabang' }}</span>
                            @else
                                <span>{{ $debt->debtorOutlet->name ?? 'Outlet' }}</span>
                            @endif
                            <span class="block text-[10px] text-gray-500 uppercase">{{ $debt->debtor_type }}</span>
                        </td>
                        <td class="py-4 px-4">
                            @if($debt->invoice_number)
                                <span class="font-mono text-white">{{ $debt->invoice_number }}</span>
                            @else
                                <span class="text-gray-500 italic">Manual</span>
                            @endif
                            @if($debt->source_type)
                                <span class="block text-[9px] bg-gray-800 text-gray-400 py-0.5 px-1.5 rounded w-max mt-1 uppercase">{{ $debt->source_type }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-semibold text-white">Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-green-400">Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-yellow-400 font-semibold">Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-gray-400 font-mono">
                            {{ $debt->due_date ? date('d-m-Y H:i', strtotime($debt->due_date)) : '-' }}
                        </td>
                        <td class="py-4 px-4">
                            @if($debt->status === 'paid')
                                <span class="inline-block bg-green-500/10 border border-green-500/30 text-green-400 py-1 px-2.5 rounded-full text-[10px] font-bold uppercase">Lunas</span>
                            @elseif($debt->status === 'partial')
                                <span class="inline-block bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 py-1 px-2.5 rounded-full text-[10px] font-bold uppercase">Partial</span>
                            @else
                                <span class="inline-block bg-red-500/10 border border-red-500/30 text-red-400 py-1 px-2.5 rounded-full text-[10px] font-bold uppercase">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($debt) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                @if($debt->status !== 'paid')
                                    <button onclick="openPaymentModal({{ json_encode($debt) }})" class="text-[#B4F481] hover:text-green-300 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                        Bayar
                                    </button>
                                @endif
                                <button onclick="openEditModal({{ json_encode($debt) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <button onclick="deleteDebt('{{ $debt->id }}')" class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-gray-500">Belum ada transaksi hutang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>



<!-- ================= MODAL BOX: DETAIL & SEJARAH BAYAR ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail & Riwayat Pembayaran</h3>
            <p class="text-[11px] text-gray-400 mt-1">Keterangan lengkap kewajiban hutang dan riwayat cicilan</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-900/50 p-4 rounded-xl">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Transaksi</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 break-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Invoice</p>
                    <p id="detail-invoice" class="text-white font-mono mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Sisa Hutang</p>
                    <p id="detail-remaining" class="text-yellow-400 font-semibold mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Jatuh Tempo</p>
                    <p id="detail-due" class="text-white mt-0.5"></p>
                </div>
            </div>

            <!-- Riwayat Pembayaran sub-table -->
            <div class="mt-4">
                <h4 class="font-bold text-white mb-2 text-xs">Riwayat Cicilan Pembayaran</h4>
                <div class="max-h-[200px] overflow-y-auto border border-gray-800 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-[10px] uppercase font-bold bg-gray-900/30">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Metode</th>
                                <th class="p-3">Referensi</th>
                                <th class="p-3">Jumlah</th>
                                <th class="p-3">Kasir</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-payments-body" class="divide-y divide-gray-800 text-[11px] text-gray-300">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <div>
                    <span class="text-gray-500 text-[10px]">Catatan:</span>
                    <p id="detail-notes" class="text-gray-400 italic font-normal mt-0.5"></p>
                </div>
                <button onclick="closeDetailModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL BOX: BAYAR HUTANG (PAYMENT) ================= -->
<div id="payment-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Catat Pembayaran Hutang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Tambahkan cicilan atau pelunasan hutang</p>
        </div>
        <form id="payment-form" onsubmit="submitPayment(event)" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="debt_id" id="payment-debt-id">

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Nominal Pembayaran <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="payment-amount" placeholder="0" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" min="1" required>
                <p id="payment-max-label" class="text-gray-500 text-[10px] mt-1"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Tanggal Bayar <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="payment_date" id="payment-date" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Metode Bayar <span class="text-red-500">*</span></label>
                    <select name="method" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                        <option value="TUNAI">TUNAI</option>
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="GIRO">GIRO</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Referensi / No. Bukti</label>
                <input type="text" name="reference" placeholder="Contoh: TRF-X8211" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Catatan</label>
                <textarea name="notes" placeholder="Catatan transaksi..." rows="2" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"></textarea>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closePaymentModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT HUTANG ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Ubah Data Hutang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Edit detail rincian transaksi hutang</p>
        </div>
        <form id="edit-form" onsubmit="submitEdit(event)" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            
            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Total Nominal <span class="text-red-500">*</span></label>
                <input type="number" name="total_amount" id="edit-total" placeholder="0" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" min="0" required>
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Jatuh Tempo</label>
                <input type="datetime-local" name="due_date" id="edit-due" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-300">Catatan</label>
                <textarea name="notes" id="edit-notes" placeholder="Catatan tambahan..." rows="2" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"></textarea>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Ubah Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Alert Utilities
    function showAlert(message, type = 'success') {
        const alertBox = document.getElementById('alert-container');
        alertBox.className = `mb-4 p-4 rounded-xl text-xs flex items-center gap-2 ${
            type === 'success' ? 'bg-green-500/10 border border-green-500/30 text-green-400' : 'bg-red-500/10 border border-red-500/30 text-red-400'
        }`;
        alertBox.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${
                    type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                }"></path>
            </svg>
            <span>${message}</span>
        `;
        alertBox.classList.remove('hidden');
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => {
                alertBox.classList.add('hidden');
                alertBox.style.opacity = '1';
            }, 500);
        }, 4000);
    }

    // Filter Logic
    function filterDebts() {
        const status = document.getElementById('filter-status').value;
        const rows = document.querySelectorAll('.debt-row');

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            let matchStatus = !status || rowStatus === status;

            if (matchStatus) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    }

    // Modal Control: Detail & Payments History
    function openDetailModal(debt) {
        document.getElementById('detail-id').textContent = debt.id;
        document.getElementById('detail-invoice').textContent = debt.invoice_number || 'Manual';
        
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        document.getElementById('detail-remaining').textContent = formatter.format(debt.remaining_amount);
        document.getElementById('detail-due').textContent = debt.due_date ? new Date(debt.due_date).toLocaleString('id-ID', {dateStyle: 'short', timeStyle: 'short'}) : '-';
        document.getElementById('detail-notes').textContent = debt.notes || 'Tidak ada catatan tambahan.';

        // Populate Payments list
        const body = document.getElementById('detail-payments-body');
        body.innerHTML = '';
        const payments = debt.payments || [];
        if (payments.length === 0) {
            body.innerHTML = `<tr><td colspan="6" class="p-3 text-center text-gray-500 italic">Belum ada cicilan pembayaran.</td></tr>`;
        } else {
            payments.forEach(p => {
                const date = new Date(p.payment_date).toLocaleDateString('id-ID');
                const creator = p.creator ? p.creator.name : 'System';
                body.innerHTML += `
                    <tr class="hover:bg-gray-800/20 transition">
                        <td class="p-3">${date}</td>
                        <td class="p-3 font-semibold text-white">${p.method}</td>
                        <td class="p-3 font-mono">${p.reference || '-'}</td>
                        <td class="p-3 text-green-400 font-semibold">${formatter.format(p.amount)}</td>
                        <td class="p-3 text-gray-400">${creator}</td>
                        <td class="p-3 text-right">
                            <button onclick="deletePayment('${p.id}')" class="text-red-500 hover:text-red-400 transition cursor-pointer">Hapus</button>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }

    async function deletePayment(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus cicilan pembayaran ini?')) return;
        try {
            const response = await fetch(`/auth/debts-payments/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            if (response.ok) {
                closeDetailModal();
                showAlert('Cicilan pembayaran berhasil dihapus.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('Gagal menghapus cicilan pembayaran.', 'error');
            }
        } catch (error) {
            showAlert('Terjadi kesalahan koneksi.', 'error');
        }
    }

    // Modal Control: Bayar Hutang (Payment creation)
    function openPaymentModal(debt) {
        document.getElementById('payment-debt-id').value = debt.id;
        document.getElementById('payment-amount').value = Math.round(debt.remaining_amount);
        document.getElementById('payment-amount').max = Math.round(debt.remaining_amount);
        
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        document.getElementById('payment-max-label').textContent = `Maksimal pembayaran: ${formatter.format(debt.remaining_amount)}`;
        
        // Auto set current date-time
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('payment-date').value = now.toISOString().slice(0, 16);

        document.getElementById('payment-modal').classList.remove('hidden');
    }
    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }

    async function submitPayment(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/auth/debts-payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data._token
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (response.ok) {
                closePaymentModal();
                showAlert('Pembayaran berhasil ditambahkan.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(result.message || 'Gagal merekam pembayaran.', 'error');
            }
        } catch (error) {
            showAlert('Terjadi kesalahan koneksi.', 'error');
        }
    }

    // Modal Control: Edit Hutang
    function openEditModal(debt) {
        document.getElementById('edit-total').value = Math.round(debt.total_amount);
        if (debt.due_date) {
            const due = new Date(debt.due_date);
            due.setMinutes(due.getMinutes() - due.getTimezoneOffset());
            document.getElementById('edit-due').value = due.toISOString().slice(0, 16);
        } else {
            document.getElementById('edit-due').value = '';
        }
        document.getElementById('edit-notes').value = debt.notes || '';
        document.getElementById('edit-form').setAttribute('data-id', debt.id);

        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    async function submitEdit(e) {
        e.preventDefault();
        const form = e.target;
        const id = form.getAttribute('data-id');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch(`/auth/debts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data._token
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (response.ok) {
                closeEditModal();
                showAlert('Data hutang berhasil diupdate.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(result.message || 'Gagal mengubah data hutang.', 'error');
            }
        } catch (error) {
            showAlert('Terjadi kesalahan koneksi.', 'error');
        }
    }

    // Delete Debt
    async function deleteDebt(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus data hutang ini? Rincian cicilan juga akan dihapus.')) return;
        try {
            const response = await fetch(`/auth/debts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            if (response.ok) {
                showAlert('Hutang berhasil dihapus.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert('Gagal menghapus hutang.', 'error');
            }
        } catch (error) {
            showAlert('Terjadi kesalahan koneksi.', 'error');
        }
    }
</script>
@endsection
