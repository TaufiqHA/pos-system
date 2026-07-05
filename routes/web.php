<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeliveriesController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\WholesalePriceController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\OutletsController;
use App\Http\Middleware\AuthCheck;
use App\Models\Deliveries;
use App\Models\Product;
use App\Models\PurchaseOrders;
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
        })->with(['branch', 'user'])->orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact('purchaseOrders'));
    })->name('admin.dashboard');
    Route::get('/monitoring-stock', [ProductStockController::class, 'index'])->name('admin.monitoring-stock');

    Route::get('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check_sku');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('product-stocks', ProductStockController::class);
    Route::resource('suppliers', SuppliersController::class);
    Route::resource('users', UserController::class);
    Route::resource('purchases', PurchasesController::class);


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
    Route::get('/dashboard', function () {
        $products = Product::orderBy('name')->get();
        $deliveries = Deliveries::whereHas('sale', function ($query) {
            $query->where('branch_id', auth()->user()->branch_id);
        })->with(['sale.salesItems'])->orderBy('created_at', 'desc')->get();

        return view('cabang.dashboard', compact('products', 'deliveries'));
    })->name('cabang.dashboard');

    Route::get('/monitoring-stok', [ProductStockController::class, 'monitoringStok'])->name('cabang.monitoring-stok');
        // Penjualan Cabang Route
        Route::get('/penjualan', [SalesController::class, 'cabangIndex'])->name('cabang.penjualan');
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
