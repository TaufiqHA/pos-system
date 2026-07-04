@extends('layouts.admin')

@section('title', 'Daftar Cabang - Lucifer POS')

@section('page_title', 'Daftar Cabang')
@section('page_subtitle', 'Mengelola cabang operasional POS')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('branches.index') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari cabang atau telepon..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
            <select name="wilayah_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Wilayah --</option>
                @foreach($wilayahs as $wilayah)
                    <option value="{{ $wilayah->id }}" {{ request('wilayah_id') == $wilayah->id ? 'selected' : '' }}>
                        {{ $wilayah->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="hidden">Search</button>
        </form>

        <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Cabang
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
                    <th class="pb-3 px-4">Nama Cabang</th>
                    <th class="pb-3 px-4">Telepon</th>
                    <th class="pb-3 px-4">Wilayah</th>
                    <th class="pb-3 px-4">Alamat</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($branches as $branch)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white">{{ $branch->name }}</td>
                        <td class="py-4 px-4 font-medium text-gray-300">{{ $branch->phone ?? '-' }}</td>
                        <td class="py-4 px-4">
                            @if($branch->wilayah)
                                <span class="bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded-full text-[10px] font-semibold border border-indigo-500/20">
                                    {{ $branch->wilayah->name }}
                                </span>
                            @else
                                <span class="text-gray-500 font-semibold">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-gray-400 max-w-[200px] truncate">{{ $branch->address ?? '-' }}</td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($branch) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($branch) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?')">
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
                        <td colspan="6" class="py-8 text-center text-gray-500">Belum ada cabang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH CABANG ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Cabang Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat cabang operasional baru</p>
        </div>
        <form action="{{ route('branches.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-name" class="block font-bold text-gray-300">Nama Cabang</label>
                    <input type="text" name="name" id="create-name" value="{{ !old('_method') ? old('name') : '' }}" placeholder="Contoh: Cabang Bandung" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="create-phone" class="block font-bold text-gray-300">Telepon</label>
                    <input type="text" name="phone" id="create-phone" value="{{ !old('_method') ? old('phone') : '' }}" placeholder="Contoh: 08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="space-y-1">
                    <label for="create-email" class="block font-bold text-gray-300">Email Akun Cabang</label>
                    <input type="email" name="email" id="create-email" value="{{ !old('_method') ? old('email') : '' }}" placeholder="email@cabang.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('email') border-red-500 @enderror" required>
                    @error('email')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="create-password" class="block font-bold text-gray-300">Password</label>
                    <input type="password" name="password" id="create-password" placeholder="Password akun" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('password') border-red-500 @enderror" required>
                    @error('password')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-1">
                <label for="create-wilayah" class="block font-bold text-gray-300">Wilayah</label>
                <select name="wilayah_id" id="create-wilayah" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('wilayah_id') border-red-500 @enderror">
                    <option value="">-- Pilih Wilayah (Opsional) --</option>
                    @foreach($wilayahs as $wilayah)
                        <option value="{{ $wilayah->id }}" {{ (!old('_method') && old('wilayah_id') == $wilayah->id) ? 'selected' : '' }}>
                            {{ $wilayah->name }}
                        </option>
                    @endforeach
                </select>
                @error('wilayah_id')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="create-address" class="block font-bold text-gray-300">Alamat</label>
                <textarea name="address" id="create-address" rows="2" placeholder="Alamat lengkap cabang" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('address') border-red-500 @enderror">{{ !old('_method') ? old('address') : '' }}</textarea>
                @error('address')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="create-notes" class="block font-bold text-gray-300">Catatan</label>
                <textarea name="notes" id="create-notes" rows="2" placeholder="Catatan tambahan" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @error('notes') border-red-500 @enderror">{{ !old('_method') ? old('notes') : '' }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
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

<!-- ================= MODAL BOX: EDIT CABANG ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Cabang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data cabang operasional</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('branches.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="edit-name" class="block font-bold text-gray-300">Nama Cabang</label>
                    <input type="text" name="name" id="edit-name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" placeholder="Contoh: Cabang Bandung" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('name')) border-red-500 @endif">
                    @if($errors->any() && old('_method') === 'PUT' && $errors->has('name'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <div class="space-y-1">
                    <label for="edit-phone" class="block font-bold text-gray-300">Telepon</label>
                    <input type="text" name="phone" id="edit-phone" value="{{ old('_method') === 'PUT' ? old('phone') : '' }}" placeholder="Contoh: 08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('phone')) border-red-500 @endif">
                    @if($errors->any() && old('_method') === 'PUT' && $errors->has('phone'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('phone') }}</p>
                    @endif
                </div>
            </div>

            <div class="space-y-1">
                <label for="edit-wilayah" class="block font-bold text-gray-300">Wilayah</label>
                <select name="wilayah_id" id="edit-wilayah" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('wilayah_id')) border-red-500 @endif">
                    <option value="">-- Pilih Wilayah (Opsional) --</option>
                    @foreach($wilayahs as $wilayah)
                        <option value="{{ $wilayah->id }}" {{ (old('_method') === 'PUT' && old('wilayah_id') == $wilayah->id) ? 'selected' : '' }}>
                            {{ $wilayah->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->any() && old('_method') === 'PUT' && $errors->has('wilayah_id'))
                    <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('wilayah_id') }}</p>
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-address" class="block font-bold text-gray-300">Alamat</label>
                <textarea name="address" id="edit-address" rows="2" placeholder="Alamat lengkap cabang" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('address')) border-red-500 @endif">{{ old('_method') === 'PUT' ? old('address') : '' }}</textarea>
                @if($errors->any() && old('_method') === 'PUT' && $errors->has('address'))
                    <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('address') }}</p>
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-notes" class="block font-bold text-gray-300">Catatan</label>
                <textarea name="notes" id="edit-notes" rows="2" placeholder="Catatan tambahan" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('notes')) border-red-500 @endif">{{ old('_method') === 'PUT' ? old('notes') : '' }}</textarea>
                @if($errors->any() && old('_method') === 'PUT' && $errors->has('notes'))
                    <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('notes') }}</p>
                @endif
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

<!-- ================= MODAL BOX: DETAIL CABANG ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Cabang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap cabang operasional</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl space-y-3">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID Cabang (UUID)</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-sm"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Nama Cabang</p>
                        <p id="detail-name" class="text-white text-sm font-semibold mt-0.5"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Telepon</p>
                        <p id="detail-phone" class="text-white text-sm font-semibold mt-0.5"></p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Wilayah</p>
                    <p id="detail-wilayah" class="text-white mt-0.5 text-sm font-semibold"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Alamat</p>
                    <p id="detail-address" class="text-white mt-0.5 whitespace-pre-wrap"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Catatan</p>
                    <p id="detail-notes" class="text-white mt-0.5 whitespace-pre-wrap"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Dibuat Pada</p>
                        <p id="detail-created" class="text-white mt-0.5"></p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Diupdate Pada</p>
                        <p id="detail-updated" class="text-white mt-0.5"></p>
                    </div>
                </div>
            </div>
            <div class="pt-4 flex items-center justify-end">
                <button onclick="closeDetailModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
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
    });

    // Create Modal
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    // Edit Modal
    function openEditModal(branch) {
        document.getElementById('edit-id').value = branch.id;
        document.getElementById('edit-name').value = branch.name || '';
        document.getElementById('edit-phone').value = branch.phone || '';
        document.getElementById('edit-wilayah').value = branch.wilayah_id || '';
        document.getElementById('edit-address').value = branch.address || '';
        document.getElementById('edit-notes').value = branch.notes || '';
        document.getElementById('edit-form').action = `/branches/${branch.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    // Detail Modal
    function openDetailModal(branch) {
        document.getElementById('detail-id').textContent = branch.id;
        document.getElementById('detail-name').textContent = branch.name || '-';
        document.getElementById('detail-phone').textContent = branch.phone || '-';
        document.getElementById('detail-wilayah').textContent = branch.wilayah ? branch.wilayah.name : '-';
        document.getElementById('detail-address').textContent = branch.address || '-';
        document.getElementById('detail-notes').textContent = branch.notes || '-';
        
        const createdDate = branch.created_at ? new Date(branch.created_at).toLocaleString('id-ID') : '-';
        const updatedDate = branch.updated_at ? new Date(branch.updated_at).toLocaleString('id-ID') : '-';
        
        document.getElementById('detail-created').textContent = createdDate;
        document.getElementById('detail-updated').textContent = updatedDate;
        
        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
