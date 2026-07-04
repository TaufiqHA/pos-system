<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesItemController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\WholesalePriceController;
use App\Http\Controllers\WilayahController;
use App\Http\Middleware\AuthCheck;
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
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/monitoring-stock', [ProductStockController::class, 'index'])->name('admin.monitoring-stock');

    Route::get('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check_sku');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('product-stocks', ProductStockController::class);
    Route::resource('suppliers', SuppliersController::class);
    Route::resource('purchases', PurchasesController::class);
    Route::resource('sales', SalesController::class);

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

    // Route untuk Sales Item
    Route::prefix('sales-items')->group(function () {
        Route::post('/', [SalesItemController::class, 'store'])->name('sales-items.store');
        Route::get('/{id}', [SalesItemController::class, 'show'])->name('sales-items.show');
        Route::put('/{id}', [SalesItemController::class, 'update'])->name('sales-items.update');
        Route::delete('/{id}', [SalesItemController::class, 'destroy'])->name('sales-items.destroy');
    });
});

// Cabang Dashboard Routes
Route::prefix('cabang')->middleware(['auth', 'role.cabang'])->group(function () {
    Route::get('/dashboard', function () {
        return view('cabang.dashboard');
    })->name('cabang.dashboard');
});

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Route yang membutuhkan proteksi login
    Route::middleware('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
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
