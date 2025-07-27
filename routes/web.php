<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnModelController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockTakeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
// Route::get('/email/verify', function () {
//     return Inertia::render('Auth/VerifyEmail');
// })->middleware(['auth'])->name('verification.notice');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
   Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Routes HANYA UNTUK OWNER
    Route::middleware('role:Owner')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'update', 'store']);
        Route::resource('roles', RoleController::class);
        Route::patch('/purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve');
        Route::patch('/sales/{sale}/approve', [SaleController::class, 'approve'])->name('sales.approve');
     Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
Route::delete('/accounts', [AccountController::class, 'destroyMultiple'])->name('accounts.destroyMultiple');
          Route::get('/reports/payable', [ReportController::class, 'payable'])->name('reports.payable');
    Route::get('/reports/receivable', [ReportController::class, 'receivable'])->name('reports.receivable');
    Route::resource('cash-transactions', CashTransactionController::class)->only(['index', 'create', 'store']);

    });

    // Routes UNTUK OWNER DAN ADMIN
    Route::middleware('role:Owner,Admin')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::delete('suppliers', [SupplierController::class, 'destroyMultiple'])->name('suppliers.destroyMultiple');
        Route::resource('customers', CustomerController::class);
        Route::delete('customers', [CustomerController::class, 'destroyMultiple'])->name('customers.destroyMultiple');
        Route::resource('products', ProductController::class);
         Route::delete('products', [ProductController::class, 'destroyMultiple'])->name('products.destroyMultiple');
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);    });
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/purchases/{purchase}/payments', [PaymentController::class, 'storePurchasePayment'])->name('payments.purchase.store');
        Route::post('/sales/{sale}/payments', [PaymentController::class, 'storeSalePayment'])->name('payments.sale.store');
        Route::resource('stock-takes', StockTakeController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('cash-transactions', CashTransactionController::class)->only(['index', 'create', 'store']);
        Route::resource('productions', ProductionController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('returns', ReturnModelController::class)->only(['index', 'create', 'store', 'show']);
         Route::get('/transactions/{type}/{id}', [ReturnModelController::class, 'getTransactionDetails'])->name('transactions.details');
    Route::resource('returns', ReturnModelController::class)->only(['index', 'create', 'store', 'show']);
});

