<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtsController;
use App\Http\Controllers\DebtsPaymentController;
use App\Http\Controllers\DeliveriesController;
use App\Http\Controllers\LaporanController;
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
use Illuminate\Support\Facades\Route;

// Login View Route
Route::get('/', function () {
    return view('login');
})->name('login')->middleware(AuthCheck::class);

// Roles Routes
Route::resource('roles', RoleController::class)->only(['store', 'update', 'destroy']);

// Branches Routes
Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);

// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth', 'role.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/monitoring-stock', [ProductStockController::class, 'index'])->name('admin.monitoring-stock');
    Route::get('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check_sku');
    Route::get('/hutang', [DebtsController::class, 'adminIndex'])->name('admin.hutang');

    Route::controller(LaporanController::class)->group(function () {
        Route::get('/laporan', 'admin')->name('admin.laporan');
        Route::get('/laporan-cabang', 'adminCabang')->name('admin.laporan-cabang');
    });

    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('upcoming-products', UpcomingProductsController::class);
    Route::resource('product-stocks', ProductStockController::class);
    Route::resource('suppliers', SuppliersController::class);
    Route::resource('units', UnitsController::class);
    Route::resource('users', UserController::class);
    Route::resource('purchases', PurchasesController::class);

    // Wholesale Prices Routes
    Route::controller(WholesalePriceController::class)->prefix('wholesale-prices')->name('wholesale-prices.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Purchase Items Routes
    Route::controller(PurchaseItemController::class)->prefix('purchase-items')->name('purchase-items.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Purchase Payments Routes
    Route::resource('purchase-payments', PurchasePaymentController::class)->only([
        'store', 'show', 'update', 'destroy',
    ]);
});

// Cabang Dashboard Routes
Route::prefix('cabang')->middleware(['auth', 'role.cabang'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'cabang'])->name('cabang.dashboard');
    Route::get('/penjualan', [SalesController::class, 'cabangIndex'])->name('cabang.penjualan');
    Route::get('/pengiriman', [DeliveriesController::class, 'pengirimanIndexCabang'])->name('cabang.pengiriman');
    Route::get('/hutang', [DebtsController::class, 'cabangIndex'])->name('cabang.hutang');
    Route::get('/laporan', [LaporanController::class, 'cabang'])->name('cabang.laporan');
    Route::get('/upcoming-products', [UpcomingProductsController::class, 'index'])->name('cabang.upcoming-products.index');

    Route::controller(ProductStockController::class)->group(function () {
        Route::get('/monitoring-stok', 'monitoringStok')->name('cabang.monitoring-stok');
        Route::put('/monitoring-stok/{id}', 'updateCabangStock')->name('cabang.monitoring-stok.update');
    });

    Route::resource('stock-histories', StockHistoriesController::class);
    Route::resource('outlets', OutletsController::class);

    // Product Branch Prices Routes
    Route::controller(ProductBranchPricesController::class)->prefix('product-branch-prices')->name('product-branch-prices.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'create')->name('create');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    // Wholesale Prices Routes for Cabang
    Route::controller(WholesalePriceController::class)->prefix('wholesale-prices')->name('cabang.wholesale-prices.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

// Outlet Dashboard Routes
Route::prefix('outlet')->middleware(['auth', 'role.outlet'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'outlet')->name('outlet.dashboard');
        Route::get('/order', 'outletOrder')->name('outlet.order');
        Route::get('/history', 'outletHistory')->name('outlet.history');
    });
    Route::get('/upcoming-products', [UpcomingProductsController::class, 'index'])->name('outlet.upcoming-products.index');
});

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Auth Routes
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

        Route::resource('sales', SalesController::class);

        // Sales Items routes
        Route::controller(SalesItemController::class)->prefix('sales-items')->name('sales-items.')->group(function () {
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        // Sales Payments routes
        Route::controller(SalesPaymentController::class)->prefix('sales-payments')->name('sales-payments.')->group(function () {
            Route::post('/', 'create')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'delete')->name('destroy');
        });
    });
});

// Routes untuk Wilayah
Route::controller(WilayahController::class)->prefix('wilayah')->name('wilayah.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'create')->name('create');
    Route::get('/{id}', 'show')->name('show');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'delete')->name('delete');
});
