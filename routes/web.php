<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\StoreSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Toko Sembako Modern (GoMart / GrabMart Style)
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

Route::match(['get', 'post'], '/login/quick/{role?}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Storefront (Public catalog & search)
Route::get('/', [CustomerController::class, 'index'])->name('home');
Route::get('/customer', fn () => redirect()->route('home'))->name('customer.home');
Route::get('/api/search', [CustomerController::class, 'liveSearch'])->name('api.search');
Route::post('/api/shipping-fee', [CustomerController::class, 'calculateShipping'])->name('api.shipping-fee');
Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout.process');
Route::get('/orders/{order}/track', [CustomerController::class, 'tracking'])->name('orders.track');
Route::get('/api/orders/{order}/status', [CustomerController::class, 'apiOrderStatus'])->name('api.orders.status');

// Store Admin POS Dashboard & Management (Admin Only)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('orders');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/api/live-orders', [AdminController::class, 'apiLiveOrders'])->name('api.live-orders');
    Route::post('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/orders/{order}/assign', [AdminController::class, 'assignCourier'])->name('orders.assign');

    // Products Management
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::post('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/{product}/toggle', [AdminController::class, 'toggleProductStatus'])->name('products.toggle');

    // Courier Dispatching & Monitoring
    Route::get('/couriers', [AdminController::class, 'courierMonitoring'])->name('couriers');

    // Analytics & Reports
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/export-report', [AdminController::class, 'exportReport'])->name('export-report');
    Route::get('/print-report', [AdminController::class, 'printReport'])->name('print-report');

    // Dynamic Store Location & Operational Settings
    Route::get('/settings', [StoreSettingController::class, 'edit'])->name('settings');
    Route::post('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
});

// Thermal Receipt
Route::get('/orders/{order}/receipt', [AdminController::class, 'receipt'])->name('orders.receipt');

// Driver / Courier Mobile App (Driver & Admin)
Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver/dashboard', [CourierController::class, 'index'])->name('driver.dashboard');
    Route::prefix('courier')->name('courier.')->group(function () {
        Route::get('/', [CourierController::class, 'index'])->name('index');
        Route::post('/{courier}/toggle-online', [CourierController::class, 'toggleOnline'])->name('toggle-online');
        Route::post('/orders/{order}/accept', [CourierController::class, 'acceptTask'])->name('accept-task');
        Route::post('/orders/{order}/start', [CourierController::class, 'startDelivery'])->name('start-delivery');
        Route::post('/orders/{order}/location', [CourierController::class, 'updateLocation'])->name('update-location');
        Route::post('/orders/{order}/complete', [CourierController::class, 'completeDelivery'])->name('complete-delivery');
    });
});

// Payments: Simulated QRIS & Midtrans Webhook
Route::post('/orders/{order}/simulate-qris-pay', [PaymentWebhookController::class, 'simulateQrisPay'])->name('payment.simulate-qris');
Route::post('/api/webhook/midtrans', [PaymentWebhookController::class, 'midtransWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('webhook.midtrans');
