<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthCheck;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WholesalePriceController;

Route::get("/", function () {
    return view("login");
})->name("login")->middleware(AuthCheck::class);

// Roles Routes
Route::post("/roles", [RoleController::class, "store"])->name("roles.store");
Route::put("/roles/{id}", [RoleController::class, "update"])->name(
    "roles.update",
);
Route::delete("/roles/{id}", [RoleController::class, "destroy"])->name(
    "roles.destroy",
);

// Branches Routes
Route::post("/branches", [BranchController::class, "store"])->name(
    "branches.store",
);
Route::put("/branches/{id}", [BranchController::class, "update"])->name(
    "branches.update",
);
Route::delete("/branches/{id}", [BranchController::class, "destroy"])->name(
    "branches.destroy",
);

// Admin Dashboard Routes
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Categories & Products Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/products/check-sku', [ProductController::class, 'checkSku'])->name('products.check_sku');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

    // Wholesale Prices Routes
    Route::post('/wholesale-prices', [WholesalePriceController::class, 'store'])->name('wholesale-prices.store');
    Route::put('/wholesale-prices/{id}', [WholesalePriceController::class, 'update'])->name('wholesale-prices.update');
    Route::delete('/wholesale-prices/{id}', [WholesalePriceController::class, 'destroy'])->name('wholesale-prices.destroy');
});

// Auth Routes
Route::prefix("auth")->group(function () {
    Route::post("/login", [AuthController::class, "login"]);

    // Route yang membutuhkan proteksi login
    Route::middleware("auth")->group(function () {
        Route::get("/me", [AuthController::class, "me"]);
        Route::post("/logout", [AuthController::class, "logout"]);
    });
});

