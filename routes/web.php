<?php

use App\Http\Controllers\LogsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerHasShopController;
use App\Http\Controllers\SkuController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false,
    'verify' => false,
    'reset' => false
]);

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/top-sellers', [DashboardController::class, 'getTopSellers'])->name('dashboard.top_sellers');

Route::middleware(['auth', 'check.access'])->group(function () {
    // Users
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/create', [UsersController::class, 'create'])->name('create');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('/{user}', [UsersController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UsersController::class, 'update'])->name('update');
        Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('index');
        Route::get('/create', [RolesController::class, 'create'])->name('create');
        Route::post('/', [RolesController::class, 'store'])->name('store');
        Route::get('/{role}', [RolesController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RolesController::class, 'update'])->name('update');
        Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfilesController::class, 'index'])->name('index');
        Route::get('/profile/password', [ProfilesController::class, 'editPassword'])->name('profile.password.edit');
        Route::put('/profile/password', [ProfilesController::class, 'updatePassword'])->name('profile.password.update');
        Route::put('/{user}', [ProfilesController::class, 'update'])->name('update');
    });

    //Order
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::post('/import', [OrderController::class, 'import'])->name('import');
        Route::post('/export', [OrderController::class, 'export'])->name('export');
        Route::delete('/delete', [OrderController::class, 'delete'])->name('delete');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    // Shop
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [SellerHasShopController::class, 'index'])->name('index');
        Route::get('/create', [SellerHasShopController::class, 'create'])->name('create');
        Route::post('/', [SellerHasShopController::class, 'store'])->name('store');
        Route::post('/check-seller', [SellerHasShopController::class, 'check_seller'])->name('check_seller');
        Route::delete('/{shop}', [SellerHasShopController::class, 'destroy'])->name('destroy');
        Route::put('/{shop}', [SellerHasShopController::class, 'update'])->name('update');
        Route::get('/{shop}/edit', [SellerHasShopController::class, 'edit'])->name('edit');

    });

    // Sku
    Route::prefix('sku')->name('sku.')->group(function () {
        Route::get('/', [SkuController::class, 'index'])->name('index');
        Route::get('/create', [SkuController::class, 'create'])->name('create');
        Route::post('/import', [SkuController::class, 'import'])->name('import');
        Route::post('/', [SkuController::class, 'store'])->name('store');
        Route::get('/{sku}/edit', [SkuController::class, 'edit'])->name('edit');
        Route::put('/{sku}', [SkuController::class, 'update'])->name('update');
        Route::delete('/{sku}', [SkuController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogsController::class, 'index'])->name('index');
        Route::get('/print', [LogsController::class, 'print'])->name('print');
    });
});
