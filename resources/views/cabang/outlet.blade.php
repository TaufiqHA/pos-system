@extends('layouts.cabang')

@section('title', 'Outlet - Cabang Lucifer POS')
@section('page_title', 'OUTLET')
@section('page_subtitle', 'Data outlet cabang')

@section('content')
    <div class="card p-6 rounded-2xl shadow-xl">
        <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
            {{-- Filter & Search Form (optional) --}}
            <form method="GET" action="{{ route('outlets.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama outlet..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20" />
                @if(request('search'))
                    <a href="{{ route('outlets.index') }}" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1 cursor-pointer">
                        Reset
                    </a>
                @endif
                <button type="submit" class="hidden">Search</button>
            </form>
            <button onclick="openCreateModal()" class="w-full sm:w-auto justify-center bg-[#B4F481] hover:bg-green-400 text-black font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-lg shadow-[#B4F481]/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Outlet
            </button>
        </div>

        @if (session('success'))
            <div id="success-alert" class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const alert = document.getElementById('success-alert');
                    if (alert) {
                        setTimeout(() => {
                            alert.classList.add('transition', 'opacity-0');
                            setTimeout(() => alert.remove(), 500);
                        }, 3000);
                    }
                });
            </script>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                        <th class="pb-3 pl-4 pr-4">No</th>
                        <th class="pb-3 px-4">Nama Outlet</th>
                        <th class="pb-3 px-4">Cabang</th>
                        <th class="pb-3 px-4">Alamat</th>
                        <th class="pb-3 px-4">Telepon</th>
                        <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                    @forelse($outlets as $outlet)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                            <td class="py-4 px-4 font-semibold text-white">{{ $outlet->name }}</td>
                            <td class="py-4 px-4 text-gray-400">{{ $outlet->branch->name ?? '-' }}</td>
                            <td class="py-4 px-4 text-gray-400">{{ $outlet->address }}</td>
                            <td class="py-4 px-4 text-gray-400">{{ $outlet->phone }}</td>
                            <td class="py-4 pl-4 pr-4 text-right">
                    <a href="javascript:void(0)" onclick="openDetailModal(this)" data-id="{{ $outlet->id }}" data-name="{{ $outlet->name }}" data-branch="{{ $outlet->branch->name ?? '' }}" data-address="{{ $outlet->address }}" data-phone="{{ $outlet->phone }}" data-email="{{ $outlet->email ?? '' }}" class="text-[#B4F481] hover:text-green-400 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">Detail</a>
                    <a href="javascript:void(0)" onclick="openEditModal(this)" data-id="{{ $outlet->id }}" data-branch_id="{{ $outlet->branch_id }}" data-branch_name="{{ $outlet->branch->name ?? '' }}" data-name="{{ $outlet->name }}" data-address="{{ $outlet->address }}" data-phone="{{ $outlet->phone }}" data-email="{{ $outlet->email ?? '' }}" class="text-blue-400 hover:text-blue-300 font-semibold transition px-2 py-1 hover:bg-blue-500/10 rounded cursor-pointer">Edit</a>
                    <form action="{{ route('outlets.destroy', $outlet->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin hapus outlet?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">Hapus</button>
                    </form>
                </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500 font-medium">Belum ada outlet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
<!-- ================= MODAL BOX: DETAIL OUTLET ================= -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Detail Outlet</h3>
            <p class="text-[11px] text-gray-400 mt-1">Informasi lengkap outlet</p>
        </div>
        <div class="space-y-4 text-xs">
            <p><strong>Nama:</strong> <span id="detail-name"></span></p>
            <p><strong>Cabang:</strong> <span id="detail-branch"></span></p>
            <p><strong>Alamat:</strong> <span id="detail-address"></span></p>
            <p><strong>Telepon:</strong> <span id="detail-phone"></span></p>
            <p><strong>Email:</strong> <span id="detail-email"></span></p>
        </div>
        <div class="pt-4 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">Tutup</button>
        </div>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT OUTLET ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Edit Outlet</h3>
            <p class="text-[11px] text-gray-400 mt-1">Ubah data outlet</p>
        </div>
        <form id="edit-form" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Cabang</label>
                    <input type="text" id="edit-branch_display" class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed" disabled>
                    <input type="hidden" name="branch_id" id="edit-branch_id">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Nama Outlet</label>
                    <input type="text" name="name" id="edit-name" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"/>
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="block font-bold text-gray-300">Alamat</label>
                    <input type="text" name="address" id="edit-address" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"/>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Telepon</label>
                    <input type="text" name="phone" id="edit-phone" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"/>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Email</label>
                    <input type="email" name="email" id="edit-email" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"/>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Password (optional, 3 - 6 karakter)</label>
                    <input type="password" name="password" id="edit-password" minlength="3" maxlength="6" placeholder="Kosongkan jika tidak diubah" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400"/>
                </div>
            </div>
            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-800">
                <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">Batal</button>
                <button type="submit" class="w-full sm:w-auto bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL BOX: TAMBAH OUTLET ================= -->
<div id="create-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-2xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeCreateModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Tambah Outlet Baru</h3>
            <p class="text-[11px] text-gray-400 mt-1">Masukkan data outlet baru</p>
        </div>
        <form action="{{ route('outlets.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="create-branch_display" class="block font-bold text-gray-300">Cabang *</label>
                    <input type="text" id="create-branch_display" value="{{ auth()->user()->branch->name ?? '-' }}" class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed" disabled>
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    @if($errors->has('branch_id'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('branch_id') }}</p>
                    @endif
                </div>
                <div class="space-y-1">
                    <label for="create-name" class="block font-bold text-gray-300">Nama Outlet *</label>
                    <input type="text" name="name" id="create-name" value="{{ old('name') }}" placeholder="Contoh: Outlet A" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('name')) border-red-500 @endif">
                    @if($errors->has('name'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('name') }}</p>
                    @endif
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label for="create-address" class="block font-bold text-gray-300">Alamat *</label>
                    <input type="text" name="address" id="create-address" value="{{ old('address') }}" placeholder="Contoh: Jl. Merdeka 123" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('address')) border-red-500 @endif">
                    @if($errors->has('address'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('address') }}</p>
                    @endif
                </div>
                <div class="space-y-1">
                    <label for="create-phone" class="block font-bold text-gray-300">Telepon *</label>
                    <input type="text" name="phone" id="create-phone" value="{{ old('phone') }}" placeholder="Contoh: 0812..." class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('phone')) border-red-500 @endif">
                    @if($errors->has('phone'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('phone') }}</p>
                    @endif
                </div>
                <div class="space-y-1">
                    <label for="create-email" class="block font-bold text-gray-300">Email *</label>
                    <input type="email" name="email" id="create-email" value="{{ old('email') }}" placeholder="Contoh: outlet@example.com" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('email')) border-red-500 @endif">
                    @if($errors->has('email'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('email') }}</p>
                    @endif
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-gray-300">Password *</label>
                    <input type="password" name="password" id="create-password" placeholder="3 - 6 karakter" minlength="3" maxlength="6" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400 @if($errors->has('password')) border-red-500 @endif" required>
                    @if($errors->has('password'))
                        <p class="text-red-500 text-[10px] mt-1">{{ $errors->first('password') }}</p>
                    @endif
                </div>
            </div>
            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-gray-800">
                <button type="button" onclick="closeCreateModal()" class="w-full sm:w-auto text-center justify-center text-gray-400 hover:text-white font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-800 transition cursor-pointer">Batal</button>
                <button type="submit" class="w-full sm:w-auto text-center justify-center bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script>
    function openCreateModal(){
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal(){
        document.getElementById('create-modal').classList.add('hidden');
    }
    function openDetailModal(el){
        document.getElementById('detail-name').textContent = el.dataset.name;
        document.getElementById('detail-branch').textContent = el.dataset.branch;
        document.getElementById('detail-address').textContent = el.dataset.address;
        document.getElementById('detail-phone').textContent = el.dataset.phone;
        document.getElementById('detail-email').textContent = el.dataset.email;
        document.getElementById('detail-modal').classList.remove('hidden');
    }
    function closeDetailModal(){
        document.getElementById('detail-modal').classList.add('hidden');
    }
    function openEditModal(el){
        const form = document.getElementById('edit-form');
        form.action = '/cabang/outlets/' + el.dataset.id;
        document.getElementById('edit-branch_display').value = el.dataset.branch_name;
        document.getElementById('edit-branch_id').value = el.dataset.branch_id;
        document.getElementById('edit-name').value = el.dataset.name;
        document.getElementById('edit-address').value = el.dataset.address;
        document.getElementById('edit-phone').value = el.dataset.phone;
        document.getElementById('edit-email').value = el.dataset.email;
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal(){
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
