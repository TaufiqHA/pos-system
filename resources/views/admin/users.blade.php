@extends('layouts.admin')

@section('title', 'Manajemen User - Lucifer POS')

@section('page_title', 'Manajemen User')
@section('page_subtitle', 'Kelola data pengguna sistem POS')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telp..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-[#B4F481] text-xs shadow-lg shadow-gray-900/20">
            
            <select name="wilayah_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-[#B4F481] text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Wilayah --</option>
                @foreach($wilayahs as $wilayah)
                    <option value="{{ $wilayah->id }}" {{ request('wilayah_id') == $wilayah->id ? 'selected' : '' }}>
                        {{ $wilayah->name }}
                    </option>
                @endforeach
            </select>

            <select name="branch_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-[#B4F481] text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Cabang --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            @if(request('search') || request('wilayah_id') || request('branch_id'))
                <a href="{{ route('users.index') }}" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1 cursor-pointer">
                    Reset
                </a>
            @endif
            <button type="submit" class="hidden">Cari</button>
        </form>

        <button onclick="openCreateModal()" class="w-full md:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah User
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
                    <th class="pb-3 px-4">Nama</th>
                    <th class="pb-3 px-4">Email</th>
                    <th class="pb-3 px-4">No. Telepon</th>
                    <th class="pb-3 px-4">Cabang</th>
                    <th class="pb-3 px-4">Wilayah</th>
                    <th class="pb-3 px-4">Outlet</th>
                    <th class="pb-3 px-4">Role</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-semibold text-white">{{ $user->name }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $user->email }}</td>
                        <td class="py-4 px-4 text-gray-400">{{ $user->phone ?? '-' }}</td>
                        
                        <!-- Cabang -->
                        <td class="py-4 px-4 text-gray-400">
                            @if($user->role && $user->role->name === 'cabang' && $user->branch)
                                {{ $user->branch->name }}
                            @elseif($user->role && $user->role->name === 'outlet' && $user->outlet && $user->outlet->branch)
                                {{ $user->outlet->branch->name }}
                            @else
                                -
                            @endif
                        </td>

                        <!-- Wilayah -->
                        <td class="py-4 px-4 text-gray-400">
                            @if($user->role && $user->role->name === 'cabang' && $user->branch && $user->branch->wilayah)
                                {{ $user->branch->wilayah->name }}
                            @elseif($user->role && $user->role->name === 'outlet' && $user->outlet && $user->outlet->branch && $user->outlet->branch->wilayah)
                                {{ $user->outlet->branch->wilayah->name }}
                            @else
                                -
                            @endif
                        </td>

                        <!-- Outlet -->
                        <td class="py-4 px-4 text-gray-400">
                            @if($user->role && $user->role->name === 'outlet' && $user->outlet)
                                {{ $user->outlet->name }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            <span class="inline-block bg-indigo-500/10 text-indigo-400 py-0.5 px-2 rounded text-[10px] font-semibold uppercase">{{ $user->role->name ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-4">
                            @if($user->status === 'active')
                                <span class="inline-block bg-green-500/10 text-green-400 py-0.5 px-2 rounded text-[10px] font-semibold">Active</span>
                            @else
                                <span class="inline-block bg-red-500/10 text-red-400 py-0.5 px-2 rounded text-[10px] font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right whitespace-nowrap">
                            <div class="flex justify-end items-center gap-2">
                                <button onclick="openDetailModal({{ json_encode($user->load('role', 'branch.wilayah', 'outlet.branch.wilayah')) }})" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">
                                    Detail
                                </button>
                                <button onclick="openEditModal({{ json_encode($user->load('role', 'branch.wilayah', 'outlet.branch.wilayah')) }})" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
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
                        <td colspan="10" class="py-8 text-center text-gray-500">Belum ada user terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH USER ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah User Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Buat akun pengguna baru untuk sistem POS</p>
        </div>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="space-y-1">
                <label for="create-name" class="block font-bold text-gray-300">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="create-name" value="{{ !old('_method') ? old('name') : '' }}" placeholder="Contoh: John Doe" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('name')) border-red-500 @endif" required>
                @if($errors->any() && !old('_method'))
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="create-email" class="block font-bold text-gray-300">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="create-email" value="{{ !old('_method') ? old('email') : '' }}" placeholder="Contoh: john@example.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('email')) border-red-500 @endif" required>
                @if($errors->any() && !old('_method'))
                    @error('email')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="create-phone" class="block font-bold text-gray-300">No. Telepon</label>
                <input type="text" name="phone" id="create-phone" value="{{ !old('_method') ? old('phone') : '' }}" placeholder="Contoh: 08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('phone')) border-red-500 @endif">
                @if($errors->any() && !old('_method'))
                    @error('phone')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="create-password" class="block font-bold text-gray-300">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="create-password" placeholder="Minimal 8 karakter" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('password')) border-red-500 @endif" required>
                @if($errors->any() && !old('_method'))
                    @error('password')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="create-role_id" class="block font-bold text-gray-300">Role <span class="text-red-500">*</span></label>
                <select name="role_id" id="create-role_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && !old('_method') && $errors->has('role_id')) border-red-500 @endif" required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (!old('_method') && old('role_id') == $role->id) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @if($errors->any() && !old('_method'))
                    @error('role_id')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="create-status" class="block font-bold text-gray-300">Status <span class="text-red-500">*</span></label>
                <select name="status" id="create-status" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                    <option value="active" {{ (!old('_method') && old('status', 'active') === 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (!old('_method') && old('status') === 'inactive') ? 'selected' : '' }}>Inactive</option>
                </select>
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

<!-- ================= MODAL BOX: EDIT USER ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit User</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data pengguna yang sudah terdaftar</p>
        </div>
        <form id="edit-form" action="{{ old('id') ? route('users.update', old('id')) : '#' }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-id" value="{{ old('id') }}">

            <div class="space-y-1">
                <label for="edit-name" class="block font-bold text-gray-300">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit-name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" placeholder="Contoh: John Doe" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('name')) border-red-500 @endif" required>
                @if($errors->any() && old('_method') === 'PUT')
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-email" class="block font-bold text-gray-300">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="edit-email" value="{{ old('_method') === 'PUT' ? old('email') : '' }}" placeholder="Contoh: john@example.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('email')) border-red-500 @endif" required>
                @if($errors->any() && old('_method') === 'PUT')
                    @error('email')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-phone" class="block font-bold text-gray-300">No. Telepon</label>
                <input type="text" name="phone" id="edit-phone" value="{{ old('_method') === 'PUT' ? old('phone') : '' }}" placeholder="Contoh: 08123456789" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('phone')) border-red-500 @endif">
                @if($errors->any() && old('_method') === 'PUT')
                    @error('phone')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-password" class="block font-bold text-gray-300">Password</label>
                <input type="password" name="password" id="edit-password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('password')) border-red-500 @endif">
                @if($errors->any() && old('_method') === 'PUT')
                    @error('password')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-role_id" class="block font-bold text-gray-300">Role <span class="text-red-500">*</span></label>
                <select name="role_id" id="edit-role_id" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->any() && old('_method') === 'PUT' && $errors->has('role_id')) border-red-500 @endif" required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (old('_method') === 'PUT' && old('role_id') == $role->id) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @if($errors->any() && old('_method') === 'PUT')
                    @error('role_id')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            <div class="space-y-1">
                <label for="edit-status" class="block font-bold text-gray-300">Status <span class="text-red-500">*</span></label>
                <select name="status" id="edit-status" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400" required>
                    <option value="active" {{ (old('_method') === 'PUT' && old('status') === 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (old('_method') === 'PUT' && old('status') === 'inactive') ? 'selected' : '' }}>Inactive</option>
                </select>
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

<!-- ================= MODAL BOX: DETAIL USER ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-lg w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail User</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap pengguna sistem</p>
        </div>
        <div class="space-y-4 text-xs">
            <div class="bg-gray-900/50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">ID User</p>
                    <p id="detail-id" class="text-white font-mono mt-0.5 text-xs select-all"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Nama</p>
                    <p id="detail-name" class="text-white text-sm font-semibold mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Email</p>
                    <p id="detail-email" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">No. Telepon</p>
                    <p id="detail-phone" class="text-white mt-0.5">-</p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Role</p>
                    <p id="detail-role" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Status</p>
                    <p id="detail-status" class="text-white mt-0.5"></p>
                </div>
                <div>
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Terdaftar Pada</p>
                    <p id="detail-created" class="text-white mt-0.5"></p>
                </div>
                <div id="detail-branch-container" class="hidden">
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Cabang & Wilayah</p>
                    <p id="detail-branch-info" class="text-white mt-0.5">-</p>
                </div>
                <div id="detail-outlet-container" class="hidden">
                    <p class="text-gray-500 font-bold uppercase tracking-wider text-[9px]">Outlet & Cabang</p>
                    <p id="detail-outlet-info" class="text-white mt-0.5">-</p>
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

        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const id = urlParams.get('id');

        if (action === 'create') {
            openCreateModal();
        } else if (action === 'edit' && id) {
            const users = @json($users);
            const user = users.find(u => u.id === id);
            if (user) {
                openEditModal(user);
            }
        }
    });

    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openEditModal(user) {
        document.getElementById('edit-id').value = user.id;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-phone').value = user.phone || '';
        document.getElementById('edit-role_id').value = user.role_id || '';
        document.getElementById('edit-status').value = user.status || 'active';
        document.getElementById('edit-form').action = `/admin/users/${user.id}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openDetailModal(user) {
        document.getElementById('detail-id').textContent = user.id;
        document.getElementById('detail-name').textContent = user.name;
        document.getElementById('detail-email').textContent = user.email;
        document.getElementById('detail-phone').textContent = user.phone || '-';
        document.getElementById('detail-role').textContent = user.role ? user.role.name : '-';
        document.getElementById('detail-status').textContent = user.status || '-';

        const branchContainer = document.getElementById('detail-branch-container');
        const outletContainer = document.getElementById('detail-outlet-container');
        
        branchContainer.classList.add('hidden');
        outletContainer.classList.add('hidden');

        if (user.role && user.role.name === 'cabang' && user.branch) {
            const wilayahName = user.branch.wilayah ? user.branch.wilayah.name : '-';
            document.getElementById('detail-branch-info').textContent = `${user.branch.name} (Wilayah: ${wilayahName})`;
            branchContainer.classList.remove('hidden');
        } else if (user.role && user.role.name === 'outlet' && user.outlet) {
            const branchName = (user.outlet.branch && user.outlet.branch.name) ? user.outlet.branch.name : '-';
            document.getElementById('detail-outlet-info').textContent = `${user.outlet.name} (Cabang: ${branchName})`;
            outletContainer.classList.remove('hidden');
        }

        const createdDate = user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-';
        document.getElementById('detail-created').textContent = createdDate;

        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }
</script>
@endsection
