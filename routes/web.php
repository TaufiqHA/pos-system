<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AuthController;

Route::get("/", function () {
    return view("login");
})->name("login");

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

// Auth Routes
Route::prefix("auth")->group(function () {
    Route::post("/login", [AuthController::class, "login"]);

    // Route yang membutuhkan proteksi login
    Route::middleware("auth")->group(function () {
        Route::get("/me", [AuthController::class, "me"]);
        Route::post("/logout", [AuthController::class, "logout"]);
    });
});
