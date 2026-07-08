<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DebtsController;
use App\Http\Controllers\DebtsPaymentController;
use App\Http\Controllers\DeliveriesController;
use App\Http\Controllers\OutletsController;
use App\Http\Controllers\ProductBranchPricesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesItemController;
use App\Http\Controllers\SalesPaymentController;
use App\Http\Controllers\StockHistoriesController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\UpcomingProductsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WholesalePriceController;
use App\Http\Controllers\WilayahController;
use App\Http\Middleware\AuthCheck;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Debts;
use App\Models\Deliveries;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrders;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login')->middleware(AuthCheck::class);

// Roles Routes
Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
Route::put('/roles/{id}', [RoleController::class, 'update'])->name(
    'roles.update',
);
Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name(
    'roles.destroy',
);

// Branches Routes
Route::get('/branches', [BranchController::class, 'index'])->name(
    'branches.index',
);
Route::post('/branches', [BranchController::class, 'store'])->name(
    'branches.store',
);
Route::put('/branches/{id}', [BranchController::class, 'update'])->name(
    'branches.update',
);
Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->name(
    'branches.destroy',
);

// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth', 'role.admin'])->group(function () {
    Route::get('/dashboard', function () {
        $purchaseOrders = PurchaseOrders::whereHas('user', function ($query) {
            $query->where('parent_id', auth()->id());
        })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Approved', 'Completed'])
                    ->orWhereDate('updated_at', '>=', now()->toDateString());
            })
            ->with(['branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate widget metrics
        $widgetOmset = Sales::where('create_by', auth()->id())->sum('grand_total');

        $omsetBulanIni = Sales::where('create_by', auth()->id())->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('grand_total');
        $omsetBulanLalu = Sales::where('create_by', auth()->id())->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('grand_total');
        $omsetTrendPercent = 0;
        if ($omsetBulanLalu > 0) {
            $omsetTrendPercent = (($omsetBulanIni - $omsetBulanLalu) / $omsetBulanLalu) * 100;
        } elseif ($omsetBulanIni > 0) {
            $omsetTrendPercent = 100.0;
        }

        $hutangSupplier = Debts::whereNotNull('supplier_id')->sum('remaining_amount');
        $hutangCabang = Debts::where('debtor_type', 'branch')->where('creditor_type', 'branch')->sum('remaining_amount');
        $userBranchId = auth()->user()->branch_id;
        $totalSku = $userBranchId
            ? Product::whereHas('productStocks', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            })->count()
            : Product::count();
        $totalStok = $userBranchId
            ? (ProductStock::where('branch_id', $userBranchId)->sum('stock') ?? 0)
            : (ProductStock::sum('stock') ?? 0);

        // Calculate dynamic monthly sales trend for the last 7 months
        $startDate = now()->subMonths(6)->startOfMonth();
        $monthlySalesData = Sales::where('create_by', auth()->id())
            ->where('date', '>=', $startDate)
            ->select('grand_total', 'date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = (int) $date->format('m');
            $yearNum = (int) $date->format('Y');

            $chartLabels[] = $indonesianMonths[$monthNum];

            $sum = $monthlySalesData->filter(function ($sale) use ($monthNum, $yearNum) {
                return $sale->date->year === $yearNum && $sale->date->month === $monthNum;
            })->sum('grand_total');

            $chartValues[] = (float) $sum;
        }

        return view('admin.dashboard', compact(
            'purchaseOrders',
            'widgetOmset',
            'omsetTrendPercent',
            'hutangSupplier',
            'hutangCabang',
            'totalSku',
            'totalStok',
            'chartLabels',
            'chartValues'
        ));
    })->name('admin.dashboard');
    Route::get('/monitoring-stock', [ProductStockController::class, 'index'])->name('admin.monitoring-stock');
    Route::get('/laporan', function () {
        $totalOmset = Sales::where('create_by', auth()->id())->sum('grand_total');
        $totalKeuntungan = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->sum(DB::raw('(price - cost) * qty')) - Sales::where('create_by', auth()->id())->sum('discount');
        $barangTerjual = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->sum('qty');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('j M');
            $chartValues[] = (float) Sales::where('create_by', auth()->id())
                ->whereDate('date', $date->toDateString())
                ->sum('grand_total');
        }

        $produkTerlaris = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->select('product_name', DB::raw('SUM(qty) as total_terjual'), DB::raw('SUM(subtotal) as total_omset'))
            ->groupBy('product_name')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $transaksiTerakhir = Sales::where('create_by', auth()->id())
            ->with('branch')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('admin.laporan', compact(
            'totalOmset',
            'totalKeuntungan',
            'barangTerjual',
            'chartLabels',
            'chartValues',
            'produkTerlaris',
            'transaksiTerakhir'
        ));
    })->name('admin.laporan');

    Route::get('/laporan-cabang', function (Request $request) {
        $tab = $request->query('tab', 'manajemen');

        $wilayahs = Wilayah::orderBy('name')->get();

        // Cascading Branch Filter - Exclude admin branches
        $branchesQuery = Branch::whereDoesntHave('users', function ($q) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'admin');
            });
        });
        if ($request->filled('wilayah_id')) {
            $branchesQuery->where('wilayah_id', $request->wilayah_id);
        }
        $branches = $branchesQuery->orderBy('name')->get();

        // Handle filter reset if branch does not belong to selected wilayah or is an admin branch
        if ($request->filled('wilayah_id') && $request->filled('branch_id')) {
            $branchExists = Branch::where('id', $request->branch_id)
                ->where('wilayah_id', $request->wilayah_id)
                ->whereDoesntHave('users', function ($q) {
                    $q->whereHas('role', function ($r) {
                        $r->where('name', 'admin');
                    });
                })
                ->exists();
            if (! $branchExists) {
                $request->merge(['branch_id' => null]);
            }
        }

        $categories = Category::orderBy('name')->get();

        $regions = [];
        $stocks = collect();
        $transactions = collect();

        if ($tab === 'manajemen') {
            $regions = Wilayah::with(['branches' => function ($q) {
                $q->withCount('outlets');
            }])->orderBy('name')->get();
        } elseif ($tab === 'stok') {
            $stocksQuery = ProductStock::with(['product.category', 'branch.wilayah'])
                ->whereHas('branch', function ($q) {
                    $q->whereDoesntHave('users', function ($qu) {
                        $qu->whereHas('role', function ($r) {
                            $r->where('name', 'admin');
                        });
                    });
                });

            if ($request->filled('wilayah_id')) {
                $stocksQuery->whereHas('branch', function ($q) use ($request) {
                    $q->where('wilayah_id', $request->wilayah_id);
                });
            }
            if ($request->filled('branch_id')) {
                $stocksQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('category_id')) {
                $stocksQuery->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $stocks = $stocksQuery->orderBy('stock', 'desc')->get();
        } elseif ($tab === 'transaksi') {
            $transactionsQuery = Sales::with(['branch.wilayah', 'outlet', 'salesItems.product'])
                ->whereNotNull('outlet_id')
                ->whereHas('branch', function ($q) {
                    $q->whereDoesntHave('users', function ($qu) {
                        $qu->whereHas('role', function ($r) {
                            $r->where('name', 'admin');
                        });
                    });
                });

            if ($request->filled('wilayah_id')) {
                $transactionsQuery->whereHas('branch', function ($q) use ($request) {
                    $q->where('wilayah_id', $request->wilayah_id);
                });
            }
            if ($request->filled('branch_id')) {
                $transactionsQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('category_id')) {
                $transactionsQuery->whereHas('salesItems.product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $transactions = $transactionsQuery->orderBy('date', 'desc')->get();
        }

        return view('admin.laporan-cabang', compact(
            'tab',
            'wilayahs',
            'branches',
            'categories',
            'regions',
            'stocks',
            'transactions'
        ));
    })->name('admin.laporan-cabang');

    Route::get('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check_sku');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('upcoming-products', UpcomingProductsController::class);
    Route::resource('product-stocks', ProductStockController::class);
    Route::resource('suppliers', SuppliersController::class);
    Route::resource('units', UnitsController::class);
    Route::resource('users', UserController::class);
    Route::resource('purchases', PurchasesController::class);
    Route::get('/hutang', [DebtsController::class, 'adminIndex'])->name('admin.hutang');

    // Wholesale Prices Routes
    Route::post('/wholesale-prices', [WholesalePriceController::class, 'store'])->name('wholesale-prices.store');
    Route::put('/wholesale-prices/{id}', [WholesalePriceController::class, 'update'])->name('wholesale-prices.update');
    Route::delete('/wholesale-prices/{id}', [WholesalePriceController::class, 'destroy'])->name('wholesale-prices.destroy');

    // Purchase Items Routes
    Route::post('/purchase-items', [PurchaseItemController::class, 'store'])->name('purchase-items.store');
    Route::get('/purchase-items/{id}', [PurchaseItemController::class, 'show'])->name('purchase-items.show');
    Route::put('/purchase-items/{id}', [PurchaseItemController::class, 'update'])->name('purchase-items.update');
    Route::delete('/purchase-items/{id}', [PurchaseItemController::class, 'destroy'])->name('purchase-items.destroy');

    // Purchase Payments Routes
    Route::resource('purchase-payments', PurchasePaymentController::class)->only([
        'store', 'show', 'update', 'destroy',
    ]);

});

// Cabang Dashboard Routes
Route::prefix('cabang')->middleware(['auth', 'role.cabang'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $pusatBranchId = auth()->user()->parent->branch_id ?? 'BRC-001';
        $branchId = auth()->user()->branch_id;

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
            'branchPrices' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
            'productStocks' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
        ])->orderBy('name')->get();
        $deliveries = Deliveries::where('created_by', '!=', auth()->id())
            ->whereHas('sale', function ($query) {
                $query->where('branch_id', auth()->user()->branch_id);
            })->with(['sale.salesItems'])->orderBy('created_at', 'desc')->get();

        // Fetch PO requests from outlets belonging to this branch
        $outletPurchaseOrders = PurchaseOrders::whereHas('outlet', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->with(['outlet', 'user', 'sale'])->orderBy('created_at', 'desc')->get();

        $selectedPoId = $request->query('outlet_po_id');
        $selectedPo = $selectedPoId ? $outletPurchaseOrders->firstWhere('id', $selectedPoId) : null;
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;

        // Calculate dynamic dashboard metrics
        $omsetPenjualan = Sales::where('branch_id', $branchId)->where('create_by', auth()->id())->sum('grand_total');
        $omsetHariIni = Sales::where('branch_id', $branchId)->where('create_by', auth()->id())->whereDate('date', now()->toDateString())->sum('grand_total');

        $hutangPusat = Debts::where('debtor_type', 'branch')
            ->where('debtor_branch_id', $branchId)
            ->where('creditor_type', 'branch')
            ->sum('remaining_amount');
        $hutangPusatCount = Debts::where('debtor_type', 'branch')
            ->where('debtor_branch_id', $branchId)
            ->where('creditor_type', 'branch')
            ->where('remaining_amount', '>', 0)
            ->count();

        $totalProduk = ProductStock::where('branch_id', $branchId)->count();
        $totalStok = ProductStock::where('branch_id', $branchId)->sum('stock') ?? 0;

        // Prepare chart data (rolling last 6 months)
        $startDate = now()->subMonths(5)->startOfMonth();
        $monthlySalesData = Sales::where('branch_id', $branchId)
            ->where('create_by', auth()->id())
            ->where('date', '>=', $startDate)
            ->select('grand_total', 'date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = (int) $date->format('m');
            $yearNum = (int) $date->format('Y');

            $chartLabels[] = $indonesianMonths[$monthNum];

            $sum = $monthlySalesData->filter(function ($sale) use ($monthNum, $yearNum) {
                return $sale->date->year === $yearNum && $sale->date->month === $monthNum;
            })->sum('grand_total');

            $chartValues[] = (float) $sum;
        }

        return view('cabang.dashboard', compact(
            'products',
            'deliveries',
            'outletPurchaseOrders',
            'selectedPo',
            'selectedPoNotes',
            'omsetPenjualan',
            'omsetHariIni',
            'hutangPusat',
            'hutangPusatCount',
            'totalProduk',
            'totalStok',
            'chartLabels',
            'chartValues'
        ));
    })->name('cabang.dashboard');

    Route::get('/monitoring-stok', [ProductStockController::class, 'monitoringStok'])->name('cabang.monitoring-stok');
    // Penjualan Cabang Route
    Route::get('/penjualan', [SalesController::class, 'cabangIndex'])->name('cabang.penjualan');
    // Pengiriman Cabang Route
    Route::get('/pengiriman', [DeliveriesController::class, 'pengirimanIndexCabang'])->name('cabang.pengiriman');
    Route::put('/monitoring-stok/{id}', [ProductStockController::class, 'updateCabangStock'])->name('cabang.monitoring-stok.update');
    Route::resource('stock-histories', StockHistoriesController::class);

    // Outlets Routes
    Route::resource('outlets', OutletsController::class);

    // Product Branch Prices Routes
    Route::get('/product-branch-prices', [ProductBranchPricesController::class, 'index'])->name('product-branch-prices.index');
    Route::post('/product-branch-prices', [ProductBranchPricesController::class, 'create'])->name('product-branch-prices.create');
    Route::get('/product-branch-prices/{id}', [ProductBranchPricesController::class, 'show'])->name('product-branch-prices.show');
    Route::put('/product-branch-prices/{id}', [ProductBranchPricesController::class, 'update'])->name('product-branch-prices.update');
    Route::delete('/product-branch-prices/{id}', [ProductBranchPricesController::class, 'delete'])->name('product-branch-prices.delete');

    // Wholesale Prices Routes for Cabang
    Route::post('/wholesale-prices', [WholesalePriceController::class, 'store'])->name('cabang.wholesale-prices.store');
    Route::put('/wholesale-prices/{id}', [WholesalePriceController::class, 'update'])->name('cabang.wholesale-prices.update');
    Route::delete('/wholesale-prices/{id}', [WholesalePriceController::class, 'destroy'])->name('cabang.wholesale-prices.destroy');

    // Hutang Cabang Route
    Route::get('/hutang', [DebtsController::class, 'cabangIndex'])->name('cabang.hutang');

    Route::get('/laporan', function () {
        $branchId = auth()->user()->branch_id;

        $totalOmset = Sales::where('branch_id', $branchId)
            ->whereNotNull('outlet_id')
            ->where('create_by', auth()->id())
            ->sum('grand_total');

        $totalKeuntungan = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->sum(DB::raw('(price - cost) * qty')) - Sales::where('branch_id', $branchId)->whereNotNull('outlet_id')->where('create_by', auth()->id())->sum('discount');

        $barangTerjual = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->sum('qty');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('j M');
            $chartValues[] = (float) Sales::where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id())
                ->whereDate('date', $date->toDateString())
                ->sum('grand_total');
        }

        $produkTerlaris = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->select('product_name', DB::raw('SUM(qty) as total_terjual'), DB::raw('SUM(subtotal) as total_omset'))
            ->groupBy('product_name')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $transaksiTerakhir = Sales::where('branch_id', $branchId)
            ->whereNotNull('outlet_id')
            ->where('create_by', auth()->id())
            ->with('outlet')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('cabang.laporan', compact(
            'totalOmset',
            'totalKeuntungan',
            'barangTerjual',
            'chartLabels',
            'chartValues',
            'produkTerlaris',
            'transaksiTerakhir'
        ));
    })->name('cabang.laporan');

    Route::get('/upcoming-products', [UpcomingProductsController::class, 'index'])->name('cabang.upcoming-products.index');
});

// Outlet Dashboard Routes
Route::prefix('outlet')->middleware(['auth', 'role.outlet'])->group(function () {
    Route::get('/dashboard', function () {
        $outletId = auth()->user()->outlet_id;
        $branchId = auth()->user()->branch_id ?? 'BRC-001';

        $deliveries = collect();
        $totalBelanja = 0;
        $totalOrder = 0;
        $chartLabels = [];
        $chartValues = [];

        if ($outletId) {
            $deliveries = Deliveries::whereHas('sale', function ($query) use ($outletId) {
                $query->where('outlet_id', $outletId);
            })->with(['sale.salesItems'])->orderBy('created_at', 'desc')->get();

            $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)->get();
            $totalOrder = $purchaseOrders->count();
            $totalBelanja = $purchaseOrders->filter(function ($po) {
                return ! in_array($po->status, ['Rejected', 'Draft']);
            })->sum(function ($po) {
                $notes = json_decode($po->notes, true);

                return (float) ($notes['grand_total'] ?? 0);
            });

            // Prepare chart data (rolling last 6 months)
            $indonesianMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
            ];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthNum = (int) $date->format('m');
                $yearNum = (int) $date->format('Y');

                $chartLabels[] = $indonesianMonths[$monthNum];

                $sum = $purchaseOrders->filter(function ($po) use ($monthNum, $yearNum) {
                    return $po->created_at->year === $yearNum && $po->created_at->month === $monthNum && ! in_array($po->status, ['Rejected', 'Draft']);
                })->sum(function ($po) {
                    $notes = json_decode($po->notes, true);

                    return (float) ($notes['grand_total'] ?? 0);
                });

                $chartValues[] = (float) $sum;
            }
        }

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'branchPrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'productStocks' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
        ])->orderBy('name')->get();

        return view('outlet.dashboard', compact('deliveries', 'products', 'totalBelanja', 'totalOrder', 'chartLabels', 'chartValues'));
    })->name('outlet.dashboard');

    Route::get('/order', function () {
        $outletId = auth()->user()->outlet_id;
        $branchId = auth()->user()->branch_id ?? 'BRC-001';

        $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)
            ->with(['branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'branchPrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'productStocks' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
        ])->orderBy('name')->get();

        return view('outlet.order', compact('purchaseOrders', 'products'));
    })->name('outlet.order');

    Route::get('/history', function () {
        $outletId = auth()->user()->outlet_id;
        $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)
            ->with(['branch', 'user', 'sale.debt'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('outlet.history', compact('purchaseOrders'));
    })->name('outlet.history');

    Route::get('/upcoming-products', [UpcomingProductsController::class, 'index'])->name('outlet.upcoming-products.index');
});

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Route yang membutuhkan proteksi login
    Route::middleware('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::resource('purchase-orders', PurchaseOrdersController::class);
        Route::delete('purchase-orders/{id}/delete', [PurchaseOrdersController::class, 'delete'])->name('purchase-orders.delete');
        Route::resource('deliveries', DeliveriesController::class);
        Route::resource('debts', DebtsController::class);
        Route::delete('debts/{id}/delete', [DebtsController::class, 'delete'])->name('debts.delete');
        Route::resource('debts-payments', DebtsPaymentController::class);
        Route::delete('debts-payments/{id}/delete', [DebtsPaymentController::class, 'delete'])->name('debts-payments.delete');
        Route::post('debts-payments/{id}/confirm', [DebtsPaymentController::class, 'confirm'])->name('debts-payments.confirm');
        Route::post('debts-payments/{id}/reject', [DebtsPaymentController::class, 'reject'])->name('debts-payments.reject');
        // Sales Routes
        Route::resource('sales', SalesController::class);

        // Sales Items routes
        Route::prefix('sales-items')->group(function () {
            Route::post('/', [SalesItemController::class, 'store'])->name('sales-items.store');
            Route::get('/{id}', [SalesItemController::class, 'show'])->name('sales-items.show');
            Route::put('/{id}', [SalesItemController::class, 'update'])->name('sales-items.update');
            Route::delete('/{id}', [SalesItemController::class, 'destroy'])->name('sales-items.destroy');
        });

        // Sales Payments routes
        Route::post('/sales-payments', [SalesPaymentController::class, 'create'])->name('sales-payments.store');
        Route::get('/sales-payments/{id}', [SalesPaymentController::class, 'show'])->name('sales-payments.show');
        Route::put('/sales-payments/{id}', [SalesPaymentController::class, 'update'])->name('sales-payments.update');
        Route::delete('/sales-payments/{id}', [SalesPaymentController::class, 'delete'])->name('sales-payments.destroy');
    });
});

// Routes untuk Wilayah
Route::prefix('wilayah')->group(function () {
    Route::get('/', [WilayahController::class, 'index'])->name('wilayah.index');
    Route::post('/', [WilayahController::class, 'create'])->name('wilayah.create');
    Route::get('/{id}', [WilayahController::class, 'show'])->name('wilayah.show');
    Route::put('/{id}', [WilayahController::class, 'update'])->name('wilayah.update');
    Route::delete('/{id}', [WilayahController::class, 'delete'])->name('wilayah.delete');
});
