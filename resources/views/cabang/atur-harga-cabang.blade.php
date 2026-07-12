@extends('layouts.cabang')

@section('title', 'Atur Harga Cabang - Cabang Lucifer POS')
@section('page_title', 'ATUR HARGA CABANG')
@section('page_subtitle', 'Sesuaikan harga jual produk khusus untuk cabang Anda')

@section('content')
<div class="card p-6 rounded-2xl shadow-xl">
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('product-branch-prices.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." class="w-full sm:w-64 bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20">
            
            <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-800 text-white rounded-xl py-2.5 px-4 focus:outline-none focus:border-green-400 text-xs shadow-lg shadow-gray-900/20 cursor-pointer">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @if(request('search') || request('category_id'))
                <a href="{{ route('product-branch-prices.index') }}" class="w-full sm:w-auto text-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs py-2.5 px-4 rounded-xl transition flex items-center gap-1 cursor-pointer">
                    Reset
                </a>
            @endif
            <button type="submit" class="hidden">Cari</button>
        </form>
    </div>

    @if (session('success'))
        <div id="success-alert" class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-xs">
            <div class="flex items-center gap-2 mb-1 font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs font-bold uppercase tracking-wider">
                    <th class="pb-3 pl-4 pr-4">No</th>
                    <th class="pb-3 px-4">Produk</th>
                    <th class="pb-3 px-4">SKU</th>
                    <th class="pb-3 px-4">Kategori</th>
                    <th class="pb-3 px-4 text-right">Harga Beli Cabang</th>
                    <th class="pb-3 px-4 text-right">Harga Jual Cabang</th>
                    <th class="pb-3 px-4 text-center">Status</th>
                    <th class="pb-3 pl-4 pr-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                @forelse($products as $product)
                    @php
                        $branchPrice = $product->branchPrices->first();
                    @endphp
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="py-4 pl-4 pr-4 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-900 border border-gray-800 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-[9px] uppercase font-bold text-gray-600">No Img</div>
                                    @endif
                                </div>
                                <div>
                                    <span class="block font-semibold text-white">{{ $product->name }}</span>
                                    @if($product->is_wholesale)
                                        <span class="inline-block bg-green-950/20 text-[#B4F481] border border-green-800/50 py-0.5 px-2 rounded-full text-[9px] font-semibold mt-1">Grosir</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-gray-400">
                            {{ $product->sku ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-gray-400">
                            {{ $product->category->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono text-gray-400">
                            Rp {{ number_format($product->latest_purchase_price ?? $product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold {{ $branchPrice ? 'text-[#B4F481]' : 'text-gray-300' }}">
                            Rp {{ number_format($branchPrice ? $branchPrice->sell_price : ($product->latest_purchase_price ?? $product->sell_price), 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($branchPrice)
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-[#B4F481]/10 text-[#B4F481] border border-[#B4F481]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#B4F481]"></span>
                                    Kustom
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    Mengikuti Pusat
                                </span>
                            @endif
                        </td>
                        <td class="py-4 pl-4 pr-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                @if($product->is_wholesale)
                                    <button onclick="openWholesaleModal('{{ $product->id }}')" class="text-green-400 hover:text-green-300 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                        Harga Grosir
                                    </button>
                                @endif

                                @if($branchPrice)
                                    <button onclick="openEditModal('{{ $product->id }}', '{{ $branchPrice->id }}', '{{ $branchPrice->sell_price }}')" class="text-yellow-400 hover:text-yellow-300 font-semibold transition px-2 py-1 hover:bg-yellow-500/10 rounded cursor-pointer">
                                        Edit
                                    </button>
                                    <form action="{{ route('product-branch-prices.delete', $branchPrice->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan harga produk ini mengikuti pusat?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 font-semibold transition px-2 py-1 hover:bg-red-500/10 rounded cursor-pointer">
                                            Reset
                                        </button>
                                    </form>
                                @else
                                    <button onclick="openEditModal('{{ $product->id }}', null, '{{ $product->latest_purchase_price ?? $product->sell_price }}')" class="text-[#B4F481] hover:text-green-400 font-semibold transition px-2 py-1 hover:bg-green-500/10 rounded cursor-pointer">
                                        Atur Harga
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>


                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">Tidak ditemukan data produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= MODAL BOX: EDIT DETAIL HARGA & GROSIR CABANG ================= -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card max-w-md w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition" type="button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Atur/Edit Harga Khusus Cabang</h3>
            <p class="text-[11px] text-gray-400 mt-1">Tetapkan harga jual kustom dan opsi grosir untuk produk ini di cabang Anda</p>
        </div>
        <form id="edit-price-form" action="" method="POST" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" id="edit-method" value="POST">
            <input type="hidden" name="product_id" id="edit-product-id">
            <input type="hidden" name="branch_id" id="edit-branch-id" value="{{ $branch->id ?? '' }}">

            <div class="space-y-1">
                <label class="block font-bold text-gray-400">Nama Produk</label>
                <input type="text" id="edit-name" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-gray-400">Harga Beli Cabang</label>
                <input type="text" id="edit-sell-price-pusat" readonly class="w-full bg-gray-800 border border-gray-800 text-gray-400 rounded-xl p-3 focus:outline-none cursor-not-allowed select-none">
            </div>

            <div class="space-y-1">
                <label for="edit-sell-price-cabang" class="block font-bold text-gray-300">Harga Jual Cabang Baru (Rp)</label>
                <input type="text" name="sell_price" id="edit-sell-price-cabang" value="" oninput="formatRupiah(this)" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                <p class="text-[10px] text-gray-500 mt-1">Catatan: Harga jual tidak boleh di bawah harga beli pusat.</p>
            </div>

            <div class="space-y-1">
                <label for="edit-is-wholesale" class="block font-bold text-gray-300">Grosir? *</label>
                <select name="is_wholesale" id="edit-is-wholesale" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-3 focus:outline-none focus:border-green-400">
                    <option value="0">Bukan Grosir</option>
                    <option value="1">Grosir</option>
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

<!-- ================= MODAL BOX: KELOLA HARGA GROSIR ================= -->
<div id="wholesale-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="card max-w-3xl w-full p-6 rounded-2xl shadow-2xl relative border border-gray-800 my-8">
        <button onclick="closeWholesaleModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="mb-6">
            <h3 class="text-base font-bold tracking-wide font-display text-white">Kelola Harga Grosir</h3>
            <p class="text-[11px] text-gray-400 mt-1">Mengelola daftar harga grosir berdasarkan cabang dan kuantitas minimum untuk produk: <span id="wholesale-product-name" class="font-bold text-[#B4F481]"></span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
            <!-- Form Tambah Harga Grosir -->
            <div class="md:col-span-1 p-4 bg-gray-900/50 rounded-2xl border border-gray-800 space-y-4">
                <h4 class="text-white font-bold text-xs border-b border-gray-800 pb-2">Tambah Harga Grosir</h4>
                <form id="wholesale-form" onsubmit="submitWholesaleForm(event)" class="space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" id="wholesale-product-id">
                    
                    <input type="hidden" name="branch_id" id="wholesale-branch-id" value="{{ auth()->user()->branch_id ?? '' }}">

                    <div class="space-y-1">
                        <label for="wholesale-min-qty" class="block font-bold text-gray-300">Min. Qty *</label>
                        <input type="number" name="min_qty" id="wholesale-min-qty" min="1" required placeholder="Contoh: 10" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>

                    <div class="space-y-1">
                        <label for="wholesale-price" class="block font-bold text-gray-300">Harga Satuan Grosir *</label>
                        <input type="text" name="price" id="wholesale-price" required oninput="formatRupiah(this)" placeholder="Contoh: 45.000" class="w-full bg-gray-900 border border-gray-800 text-white rounded-xl p-2.5 focus:outline-none focus:border-green-400">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#B4F481] hover:bg-green-400 text-black font-bold py-2.5 px-4 rounded-xl transition shadow-lg shadow-[#B4F481]/20 cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Harga
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Harga Grosir Aktif -->
            <div class="md:col-span-2 p-4 bg-gray-900/50 rounded-2xl border border-gray-800 flex flex-col">
                <h4 class="text-white font-bold text-xs border-b border-gray-800 pb-2 mb-3">Daftar Harga Grosir Terdaftar</h4>
                <div class="overflow-x-auto flex-1 max-h-64">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-[10px] font-bold uppercase tracking-wider">
                                <th class="pb-2">Cabang</th>
                                <th class="pb-2 text-center">Min. Qty</th>
                                <th class="pb-2 text-right">Harga Grosir</th>
                                <th class="pb-2 text-right pr-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wholesale-list-body" class="divide-y divide-gray-800 text-gray-300">
                            <!-- Diisi dinamis lewat JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pt-4 mt-6 flex items-center justify-end border-t border-gray-800">
            <button onclick="closeWholesaleModal()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-xl transition cursor-pointer">
                Selesai
            </button>
        </div>
    </div>
</div>

<script>
    // Master data produk & cabang dari server
    let productsData = @json($products);
    const branchesData = [@json($branch)];

    // Edit Modal Functions
    function openEditModal(productId, branchPriceId, currentPrice) {
        const product = productsData.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('edit-product-id').value = product.id;
        document.getElementById('edit-name').value = product.name;
        const buyPrice = product.latest_purchase_price !== null ? product.latest_purchase_price : product.sell_price;
        document.getElementById('edit-sell-price-pusat').value = 'Rp ' + parseInt(buyPrice, 10).toLocaleString('id-ID').replace(/,/g, '.');
        
        const priceInput = document.getElementById('edit-sell-price-cabang');
        priceInput.value = currentPrice;
        formatRupiah(priceInput);

        document.getElementById('edit-is-wholesale').value = branchPriceId ? (product.is_wholesale ? '1' : '0') : '0';

        const form = document.getElementById('edit-price-form');
        const methodInput = document.getElementById('edit-method');

        if (branchPriceId) {
            // Update route
            form.action = `/cabang/product-branch-prices/${branchPriceId}`;
            methodInput.value = 'PUT';
        } else {
            // Create route
            form.action = '/cabang/product-branch-prices';
            methodInput.value = 'POST';
        }

        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    // Wholesale Price Modal Functions
    let currentWholesaleProductId = null;
    let currentWholesalePrices = [];

    function openWholesaleModal(productId) {
        const product = productsData.find(p => p.id === productId);
        if (!product) return;

        currentWholesaleProductId = productId;
        currentWholesalePrices = product.wholesale_prices || [];

        document.getElementById('wholesale-product-id').value = productId;
        document.getElementById('wholesale-product-name').textContent = `${product.name} (${product.sku})`;
        
        // Reset form
        document.getElementById('wholesale-form').reset();

        renderWholesalePricesList();

        document.getElementById('wholesale-modal').classList.remove('hidden');
    }

    function closeWholesaleModal() {
        document.getElementById('wholesale-modal').classList.add('hidden');
    }

    function renderWholesalePricesList() {
        const listBody = document.getElementById('wholesale-list-body');
        listBody.innerHTML = '';

        if (currentWholesalePrices.length === 0) {
            listBody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-4 text-center text-gray-500">Belum ada harga grosir untuk produk ini.</td>
                </tr>
            `;
            return;
        }

        // Sort by branch name and min_qty
        const sortedPrices = [...currentWholesalePrices].sort((a, b) => {
            const nameA = (a.branch ? a.branch.name : '').toLowerCase();
            const nameB = (b.branch ? b.branch.name : '').toLowerCase();
            if (nameA !== nameB) return nameA.localeCompare(nameB);
            return a.min_qty - b.min_qty;
        });

        sortedPrices.forEach(wp => {
            const branchName = wp.branch ? wp.branch.name : 'Semua Cabang';
            const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(wp.price);
            
            const row = `
                <tr class="hover:bg-gray-850/30 transition">
                    <td class="py-2.5">${branchName}</td>
                    <td class="py-2.5 text-center font-bold">${wp.min_qty}</td>
                    <td class="py-2.5 text-right font-bold text-[#B4F481]">${formattedPrice}</td>
                    <td class="py-2.5 text-right pr-2">
                        <button type="button" onclick="deleteWholesalePrice('${wp.id}')" class="text-red-500 hover:text-red-400 font-semibold transition px-2 py-0.5 hover:bg-red-500/10 rounded cursor-pointer">
                            Hapus
                        </button>
                    </td>
                </tr>
            `;
            listBody.insertAdjacentHTML('beforeend', row);
        });
    }

    async function submitWholesaleForm(event) {
        event.preventDefault();

        const form = document.getElementById('wholesale-form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const productId = document.getElementById('wholesale-product-id').value;
        const branchId = document.getElementById('wholesale-branch-id').value;
        const minQty = document.getElementById('wholesale-min-qty').value;
        
        // Clean price input of dots
        let priceRaw = document.getElementById('wholesale-price').value;
        const price = priceRaw.replace(/\./g, '');

        // Client-side validation: wholesale price cannot be below product buy price
        const product = productsData.find(p => p.id === productId);
        if (product && parseFloat(price) < parseFloat(product.buy_price)) {
            const formattedBuyPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(product.buy_price);
            alert(`Harga grosir tidak boleh di bawah harga beli produk (${formattedBuyPrice})`);
            return;
        }

        submitBtn.disabled = true;
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Menyimpan...';

        try {
            const response = await fetch('{{ route("cabang.wholesale-prices.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    branch_id: branchId,
                    min_qty: minQty,
                    price: price
                })
            });

            const data = await response.json();

            if (response.status === 201) {
                // Success! Get branch object info from branchesData
                const branchObj = branchesData.find(b => b.id === branchId);
                const newPriceItem = data.data;
                newPriceItem.branch = branchObj; // attach branch relation representation for UI

                // Update productsData and currentWholesalePrices
                const product = productsData.find(p => p.id === productId);
                if (product) {
                    if (!product.wholesale_prices) product.wholesale_prices = [];
                    product.wholesale_prices.push(newPriceItem);
                    currentWholesalePrices = product.wholesale_prices;
                } else {
                    currentWholesalePrices.push(newPriceItem);
                }

                renderWholesalePricesList();
                form.reset();
                
                // Show floating small notification
                showToast('Harga grosir berhasil ditambahkan!');
            } else {
                alert(data.message || 'Gagal menambahkan harga grosir');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        }
    }

    async function deleteWholesalePrice(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus harga grosir ini?')) {
            return;
        }

        try {
            const response = await fetch(`/cabang/wholesale-prices/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Remove from local memory lists
                currentWholesalePrices = currentWholesalePrices.filter(wp => wp.id !== id);
                
                const product = productsData.find(p => p.id === currentWholesaleProductId);
                if (product) {
                    product.wholesale_prices = product.wholesale_prices.filter(wp => wp.id !== id);
                }

                renderWholesalePricesList();
                showToast('Harga grosir berhasil dihapus!');
            } else {
                alert(data.message || 'Gagal menghapus harga grosir');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan.');
        }
    }

    function showToast(message) {
        // Create dynamic toast notification matching modern aesthetics
        let toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 z-55 bg-gray-900 border border-green-500/30 text-green-400 p-4 rounded-xl text-xs flex items-center gap-2 shadow-2xl transition duration-500 transform translate-y-10 opacity-0';
        toast.innerHTML = `
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        
        // Trigger reflow & animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        }, 10);

        // Animate out & remove
        setTimeout(() => {
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 3000);
    }

    function formatRupiah(element) {
        // Hapus karakter selain angka
        let value = element.value.replace(/[^0-9]/g, '');
        
        // Tambahkan titik setiap 3 angka (format ribuan Indonesia)
        if (value) {
            element.value = parseInt(value, 10).toLocaleString('id-ID').replace(/,/g, '.');
        } else {
            element.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Auto-format any pre-filled/validation-failed price inputs on load
        const priceInputsOnLoad = document.querySelectorAll('input[name="sell_price"]');
        priceInputsOnLoad.forEach(input => {
            if (input.value) {
                formatRupiah(input);
            }
        });

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const priceInputs = form.querySelectorAll('input[name="sell_price"], input[name="price"]');
                priceInputs.forEach(input => {
                    if (input.value) {
                        // Hapus semua titik agar menjadi angka murni untuk dikirim ke backend
                        input.value = input.value.replace(/\./g, '');
                    }
                });
            });
        });

        // Auto-hide success alert after 3 seconds
        const alert = document.getElementById('success-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }
    });
</script>
@endsection
