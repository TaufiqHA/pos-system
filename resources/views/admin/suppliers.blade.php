@extends('layouts.admin')

@section('title', 'Daftar Supplier - Lucifer POS')

@section('page_title', 'Daftar Supplier')
@section('page_subtitle', 'Mengelola mitra dan pemasok barang POS')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <!-- Breadcrumbs or simple search can go here if needed -->
        </div>
        <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Supplier
        </button>
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
                    <th class="pb-3 px-4">Nama Supplier</th>
                    <th class="pb-3 px-4">No. HP</th>
                    <th class="pb-3 px-4">Email</th>
                    <th class="pb-3 px-4">Alamat</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white">
                            <div>
                                <span class="block">{{ $supplier->name }}</span>
                                @if($supplier->notes)
                                    <span class="inline-block bg-gray-800 text-gray-400 py-0.5 px-1.5 rounded text-[9px] mt-1 font-normal max-w-[150px] truncate" title="{{ $supplier->notes }}">{{ $supplier->notes }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-gray-400">{{ $supplier->phone ?? '-' }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $supplier->email ?? '-' }}</td>
                        <td class="py-4 px-4 text-gray-400 max-w-[200px] truncate" title="{{ $supplier->address }}">{{ $supplier->address ?? '-' }}</td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($supplier) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($supplier) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')">
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
                        <td colspan="7" class="py-8 text-center text-gray-500">Belum ada supplier terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH SUPPLIER ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Supplier Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat data supplier baru untuk pemesanan barang</p>
        </div>
        <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div class="space-y-1">
                <label for="create-name" class="block font-bold text-gray-300">Nama Supplier <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="create-name" value="{{ !old('_method') ? old('name') : '' }}" placeholder="Contoh: PT. Sumber Makmur" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('name')) border-red-500 @endif" required>
                @if($errors->any() && !old('_method'))
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-phone" class="block font-bold text-gray-300">No. HP / Telepon</label>
                    <input type="text" name="phone" id="create-phone" value="{{ !old('_method') ? old('phone') : '' }}" placeholder="Contoh: 08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('phone')) border-red-500 @endif">
                    @if($errors->any() && !old('_method'))
                        @error('phone')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="create-email" class="block font-bold text-gray-300">Email</label>
                    <input type="email" name="email" id="create-email" value="{{ !old('_method') ? old('email') : '' }}" placeholder="Contoh: info@sumbermakmur.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('email')) border-red-500 @endif">
                    @if($errors->any() && !old('_method'))
                        @error('email')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            <div class="space-y-1">
                <label for="create-address" class="block font-bold text-gray-300">Alamat</label>
                <textarea name="address" id="create-address" rows="3" placeholder="Alamat lengkap supplier..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ !old('_method') ? old('address') : '' }}</textarea>
            </div>

            <div class="space-y-1">
                <label for="create-notes" class="block font-bold text-gray-300">Catatan Tambahan</label>
                <textarea name="notes" id="create-notes" rows="2" placeholder="Catatan khusus, syarat pembayaran, dll..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ !old('_method') ? old('notes') : '' }}</textarea>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <button type="button" onclick="closeCreateModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT SUPPLIER ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Supplier</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data supplier yang sudah terdaftar</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('suppliers.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <div class="space-y-1">
                <label for="edit-name" class="block font-bold text-gray-300">Nama Supplier <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit-name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" placeholder="PT. Sumber Makmur" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('name')) border-red-500 @endif" required>
                @if($errors->any() && old('_method') === 'PUT')
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-phone" class="block font-bold text-gray-300">No. HP / Telepon</label>
                    <input type="text" name="phone" id="edit-phone" value="{{ old('_method') === 'PUT' ? old('phone') : '' }}" placeholder="08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('phone')) border-red-500 @endif">
                    @if($errors->any() && old('_method') === 'PUT')
                        @error('phone')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-email" class="block font-bold text-gray-300">Email</label>
                    <input type="email" name="email" id="edit-email" value="{{ old('_method') === 'PUT' ? old('email') : '' }}" placeholder="info@sumbermakmur.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('email')) border-red-500 @endif">
                    @if($errors->any() && old('_method') === 'PUT')
                        @error('email')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            <div class="space-y-1">
                <label for="edit-address" class="block font-bold text-gray-300">Alamat</label>
                <textarea name="address" id="edit-address" rows="3" placeholder="Alamat lengkap supplier..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ old('_method') === 'PUT' ? old('address') : '' }}</textarea>
            </div>

            <div class="space-y-1">
                <label for="edit-notes" class="block font-bold text-gray-300">Catatan Tambahan</label>
                <textarea name="notes" id="edit-notes" rows="2" placeholder="Catatan khusus, syarat pembayaran, dll..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">{{ old('_method') === 'PUT' ? old('notes') : '' }}</textarea>
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

<!-- ================= MODAL BOX: DETAIL SUPPLIER ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Supplier</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap profil pemasok</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Supplier</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-xs select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Nama Supplier</p>
                    <p id="detail-name" class="text-white text-sm font-semibold mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">No. Telepon / HP</p>
                    <p id="detail-phone" class="text-white font-mono mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Email</p>
                    <p id="detail-email" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Terdaftar Pada</p>
                    <p id="detail-created" class="text-white mt-0.5"></p>
                </div>
            </div>

            <div class="bg-gray-900/50 p-4 rounded-xl space-y-3">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Alamat Lengkap</p>
                    <p id="detail-address" class="text-white mt-0.5 whitespace-pre-wrap"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Catatan Khusus</p>
                    <p id="detail-notes" class="text-white mt-0.5 whitespace-pre-wrap"></p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Auto hide success alert after 3 seconds
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

        // Auto open create/edit modal from query strings if present
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const id = urlParams.get('id');

        if (action === 'create') {
            openCreateModal();
        } else if (action === 'edit' && id) {
            // Find supplier object matching this id from the list
            const suppliers = @json($suppliers);
            const supplier = suppliers.find(s => s.id === id);
            if (supplier) {
                openEditModal(supplier);
            }
        }
    });

    // Create Modal
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
        // Clean URL params to avoid re-opening
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Edit Modal
    function openEditModal(supplier) {
        document.getElementById('edit-id').value = supplier.id;
        document.getElementById('edit-name').value = supplier.name;
        document.getElementById('edit-phone').value = supplier.phone || '';
        document.getElementById('edit-email').value = supplier.email || '';
        document.getElementById('edit-address').value = supplier.address || '';
        document.getElementById('edit-notes').value = supplier.notes || '';
        document.getElementById('edit-form').action = `/suppliers/${supplier.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        // Clean URL params to avoid re-opening
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Detail Modal
    function openDetailModal(supplier) {
        document.getElementById('detail-id').textContent = supplier.id;
        document.getElementById('detail-name').textContent = supplier.name;
        document.getElementById('detail-phone').textContent = supplier.phone || '-';
        document.getElementById('detail-email').textContent = supplier.email || '-';
        document.getElementById('detail-address').textContent = supplier.address || '-';
        document.getElementById('detail-notes').textContent = supplier.notes || '-';
        
        const createdDate = supplier.created_at ? new Date(supplier.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('detail-created').textContent = createdDate;
        
        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
