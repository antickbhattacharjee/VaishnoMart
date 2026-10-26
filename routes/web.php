<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryAgentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - VaishnoMart Platform
|--------------------------------------------------------------------------
*/

// Public Catalogue & Homepage Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/location/set', [HomeController::class, 'setLocation'])->name('location.set');
Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category.show');
Route::get('/store/{id}', [HomeController::class, 'vendor'])->name('vendor.show')->whereNumber('id');
Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product.show');
Route::get('/search', [HomeController::class, 'search'])->name('search');

// RAG AI Assistant Chatbot Routes
Route::prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('/message', [ChatbotController::class, 'ask'])->name('message');
    Route::get('/history', [ChatbotController::class, 'history'])->name('history');
    Route::post('/clear', [ChatbotController::class, 'clear'])->name('clear');
});

// Shopping Cart (Available to guests & logged in customers)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('update')->whereNumber('id');
    Route::post('/remove/{id}', [CartController::class, 'remove'])->name('remove')->whereNumber('id');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/vendor-register', [AuthController::class, 'showVendorRegister'])->name('vendor.register');
    Route::post('/vendor-register', [AuthController::class, 'registerVendor']);

    Route::get('/delivery-register', [AuthController::class, 'showAgentRegister'])->name('agent.register');
    Route::post('/delivery-register', [AuthController::class, 'registerAgent']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Protected Routes
Route::middleware(['auth', 'role:customer,super_admin'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');

    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [CustomerController::class, 'orderDetails'])->name('orders.show')->whereNumber('id');
        Route::post('/orders/{id}/cancel', [CustomerController::class, 'cancelOrder'])->name('orders.cancel')->whereNumber('id');
        Route::post('/orders/{id}/review', [CustomerController::class, 'submitReview'])->name('orders.review')->whereNumber('id');
        Route::post('/orders/{id}/resend-otp', [CustomerController::class, 'resendDeliveryOtp'])->name('orders.resend-otp')->middleware('throttle:otp')->whereNumber('id');
        Route::get('/addresses', [CustomerController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [CustomerController::class, 'saveAddress'])->name('addresses.save');
        Route::post('/addresses/{id}/delete', [CustomerController::class, 'deleteAddress'])->name('addresses.delete')->whereNumber('id');
    });
});

// Vendor Dashboard Routes
Route::middleware(['auth', 'role:vendor,super_admin'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [VendorController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [VendorController::class, 'orderDetails'])->name('orders.show')->whereNumber('id');
    Route::post('/orders/{id}/accept', [VendorController::class, 'acceptOrder'])->name('orders.accept')->whereNumber('id');
    Route::post('/orders/{id}/reject', [VendorController::class, 'rejectOrder'])->name('orders.reject')->whereNumber('id');
    Route::post('/orders/{id}/start-preparing', [VendorController::class, 'startPreparing'])->name('orders.start-preparing')->whereNumber('id');
    Route::post('/orders/{id}/ready-for-pickup', [VendorController::class, 'readyForPickup'])->name('orders.ready-for-pickup')->whereNumber('id');
    Route::post('/orders/{id}/resend-otp', [VendorController::class, 'resendPickupOtp'])->name('orders.resend-otp')->middleware('throttle:otp')->whereNumber('id');

    Route::get('/products', [VendorController::class, 'products'])->name('products.index');
    Route::get('/products/create', [VendorController::class, 'createProduct'])->name('products.create');
    Route::post('/products/store', [VendorController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [VendorController::class, 'editProduct'])->name('products.edit')->whereNumber('id');
    Route::post('/products/{id}/update', [VendorController::class, 'updateProduct'])->name('products.update')->whereNumber('id');

    Route::post('/store-status/toggle', [VendorController::class, 'toggleStoreStatus'])->name('store-status.toggle');
    Route::get('/earnings', [VendorController::class, 'earnings'])->name('earnings');
    Route::post('/settlements/request', [VendorController::class, 'requestSettlement'])->name('settlements.request');
});

// Delivery Agent Dashboard Routes (Mobile-Optimized)
Route::middleware(['auth', 'role:delivery_agent,super_admin'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [DeliveryAgentController::class, 'dashboard'])->name('dashboard');
    Route::post('/online/toggle', [DeliveryAgentController::class, 'toggleOnline'])->name('online.toggle');
    Route::post('/deliveries/accept/{id}', [DeliveryAgentController::class, 'acceptDelivery'])->name('deliveries.accept')->whereNumber('id');
    Route::get('/current-delivery', [DeliveryAgentController::class, 'currentOrder'])->name('current-order');
    Route::post('/deliveries/{id}/verify-pickup', [DeliveryAgentController::class, 'verifyPickup'])->name('deliveries.verify-pickup')->middleware('throttle:otp')->whereNumber('id');
    Route::post('/deliveries/{id}/verify-delivery', [DeliveryAgentController::class, 'verifyDelivery'])->name('deliveries.verify-delivery')->middleware('throttle:otp')->whereNumber('id');
    Route::post('/deliveries/{id}/resend-otp', [DeliveryAgentController::class, 'resendOtp'])->name('deliveries.resend-otp')->middleware('throttle:otp')->whereNumber('id');
    Route::get('/history', [DeliveryAgentController::class, 'history'])->name('history');
    Route::get('/earnings', [DeliveryAgentController::class, 'earnings'])->name('earnings');
});

// Super Admin Platform Management Routes
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [AdminController::class, 'orderDetails'])->name('orders.show')->whereNumber('id');
    Route::get('/vendors', [AdminController::class, 'vendors'])->name('vendors.index');
    Route::post('/vendors/{id}/status', [AdminController::class, 'updateVendorStatus'])->name('vendors.status')->whereNumber('id');
    Route::get('/agents', [AdminController::class, 'agents'])->name('agents.index');
    Route::post('/agents/{id}/status', [AdminController::class, 'updateAgentStatus'])->name('agents.status')->whereNumber('id');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::post('/categories/store', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/settlements', [AdminController::class, 'settlements'])->name('settlements.index');
    Route::post('/settlements/{id}/update', [AdminController::class, 'updateSettlement'])->name('settlements.update')->whereNumber('id');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    Route::post('/settings/update', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
});
