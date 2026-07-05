@extends('layouts.admin')

@section('title', 'Daftar Pengiriman - Lucifer POS')

@section('page_title', 'Daftar Pengiriman')
@section('page_subtitle', 'Mengelola pengiriman barang')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">

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
                    <th class="pb-3 px-4">Invoice Penjualan</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 px-4">Dikirim</th>
                    <th class="pb-3 px-4">Diterima</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white font-mono">{{ $delivery->sale?->invoice ?? '-' }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold
                                @if($delivery->status === 'DITERIMA') bg-green-500/20 text-green-400
                                @elseif($delivery->status === 'DIKIRIM') bg-blue-500/20 text-blue-400
                                @elseif($delivery->status === 'PENDING') bg-yellow-500/20 text-yellow-400
                                @else bg-red-500/20 text-red-400 @endif">
                                {{ strtoupper($delivery->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-gray-400">{{ $delivery->sent_at ? \Carbon\Carbon::parse($delivery->sent_at)->format('d-m-Y H:i') : '-' }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $delivery->received_at ? \Carbon\Carbon::parse($delivery->received_at)->format('d-m-Y H:i') : '-' }}</td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($delivery) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($delivery) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('deliveries.destroy', $delivery->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengiriman ini?')">
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
                        <td colspan="6" class="py-8 text-center text-gray-500">Belum ada data pengiriman terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<!-- ================= MODAL BOX: EDIT PENGIRIMAN ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Pengiriman</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data pengiriman</p>
        </div>
        <form id="edit-form" action="#" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label for="edit-sale_invoice" class="block font-bold text-gray-300">Penjualan (Tidak dapat diubah)</label>
                <input type="text" id="edit-sale_invoice" readonly class="w-full bg-gray-800 border border-gray-850 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed">
            </div>



            <div class="space-y-1">
                <label for="edit-status" class="block font-bold text-gray-300">Status <span class="text-red-500">*</span></label>
                <select name="status" id="edit-status" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                    <option value="PENDING">PENDING</option>
                    <option value="DIKIRIM">DIKIRIM</option>
                    <option value="DITERIMA">DITERIMA</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-sent_at" class="block font-bold text-gray-300">Tanggal Kirim</label>
                    <input type="datetime-local" name="sent_at" id="edit-sent_at" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>
                <div class="space-y-1">
                    <label for="edit-received_at" class="block font-bold text-gray-300">Tanggal Diterima</label>
                    <input type="datetime-local" name="received_at" id="edit-received_at" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                </div>
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

<!-- ================= MODAL BOX: DETAIL PENGIRIMAN ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 max-h-[90vh] overflow-y-auto">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Pengiriman</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap data pengiriman</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Pengiriman</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-[10px] select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Invoice Penjualan</p>
                    <p id="detail-invoice" class="text-white font-mono font-bold mt-0.5 text-xs select-all"></p>
                </div>

                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Status</p>
                    <span id="detail-status" class="inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase"></span>
                </div>
            </div>

            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Tanggal Kirim</p>
                    <p id="detail-sent_at" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Tanggal Diterima</p>
                    <p id="detail-received_at" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Terdaftar Pada</p>
                    <p id="detail-created" class="text-white mt-0.5"></p>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end">
                <button onclick="closeDetailModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const allSales = @json($sales);

    document.addEventListener('DOMContentLoaded', function() {
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

        const statusSelect = document.getElementById('edit-status');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                if (this.value === 'DIKIRIM') {
                    const sentAtInput = document.getElementById('edit-sent_at');
                    if (sentAtInput && !sentAtInput.value) {
                        sentAtInput.value = toDatetimeLocal(new Date());
                    }
                }
            });
        }
    });

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
    }

    function toDatetimeLocal(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function openEditModal(delivery) {
        document.getElementById('edit-form').action = `/admin/deliveries/${delivery.id}`;
        document.getElementById('edit-status').value = delivery.status;
        document.getElementById('edit-sent_at').value = toDatetimeLocal(delivery.sent_at);
        document.getElementById('edit-received_at').value = toDatetimeLocal(delivery.received_at);

        const invoiceInput = document.getElementById('edit-sale_invoice');
        if (invoiceInput) {
            invoiceInput.value = delivery.sale ? delivery.sale.invoice : 'Tanpa Penjualan';
        }

        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openDetailModal(delivery) {
        document.getElementById('detail-id').textContent = delivery.id;
        document.getElementById('detail-invoice').textContent = delivery.sale ? delivery.sale.invoice : '-';
        document.getElementById('detail-sent_at').textContent = formatDateTime(delivery.sent_at);
        document.getElementById('detail-received_at').textContent = formatDateTime(delivery.received_at);

        const createdDate = delivery.created_at ? new Date(delivery.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('detail-created').textContent = createdDate;

        const statusEl = document.getElementById('detail-status');
        statusEl.textContent = delivery.status;
        statusEl.className = 'inline-block px-2.5 py-1 rounded text-[10px] font-bold mt-1 uppercase ';
        if (delivery.status === 'DITERIMA') {
            statusEl.classList.add('bg-green-500/20', 'text-green-400');
        } else if (delivery.status === 'DIKIRIM') {
            statusEl.classList.add('bg-blue-500/20', 'text-blue-400');
        } else if (delivery.status === 'PENDING') {
            statusEl.classList.add('bg-yellow-500/20', 'text-yellow-400');
        } else {
            statusEl.classList.add('bg-red-500/20', 'text-red-400');
        }

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
