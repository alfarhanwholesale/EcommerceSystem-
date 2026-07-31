<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Temporary Route to Run Migrations on Live Server (Hostinger)
Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', [
            '--force' => true
        ]);

        // Also seed the default categories
        if (\App\Models\Category::count() == 0) {
            \App\Models\Category::insert([
                ['name' => 'Dates'],
                ['name' => 'Honey'],
                ['name' => 'Perfume'],
                ['name' => 'Bakhoor'],
                ['name' => 'Others']
            ]);
        }

        return 'Migrations ran successfully! Categories seeded. You can now go back to your website.';
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});

// ─────────────────────────────────────────────────────────────────────────────
//  Customer Storefront Front-facing Catalog (PUBLIC)
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [ProductController::class, 'home'])->name('shop.home');
Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('shop.show');
Route::get('/about', function () {
    return view('shop.about');
})->name('shop.about');

// ─────────────────────────────────────────────────────────────────────────────
//  Shopping Cart & Checkout — open to GUESTS and logged-in users
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('shop.cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update-selection', [CartController::class, 'updateSelection'])->name('cart.update-selection');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/shipping-rates', [CheckoutController::class, 'getShippingRates'])->name('checkout.shipping_rates');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{id?}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Online Banking (FPX) & eWallet payment via gateway (Guest & Auth)
// IMPORTANT: /status route must be declared BEFORE /{order_id} to avoid Laravel
// matching "status" as the {order_id} wildcard parameter (causes 404)
Route::get('/checkout/payment/status', [PaymentController::class, 'status'])->name('checkout.payment.status');
Route::get('/checkout/payment/{order_id}', [PaymentController::class, 'checkout'])->name('checkout.payment');

// ─────────────────────────────────────────────────────────────────────────────
//  Authentication Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Customer Auth
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/google', [AuthController::class, 'loginWithGoogle'])->name('login.google');
    Route::get('/auth/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.callback');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Admin Auth Login
    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});

Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

// Payment gateway & courier webhook (must be outside auth — called by ToyyibPay/Billplz/EasyParcel servers)
Route::post('/webhook/payment', [PaymentController::class, 'webhook'])->name('webhook.payment');
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

// ─────────────────────────────────────────────────────────────────────────────
//  Logged-in Customer Features (Coupons, Order History)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Email Verification Notice & Verification Actions
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');

    // Verified Customer & Administrative Features
    Route::middleware('verified')->group(function () {
        // Coupon Actions (login required to claim/apply coupons)
        Route::post('/coupon/claim', [CartController::class, 'claimCoupon'])->name('coupon.claim');
        Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
        Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');

        // Customer Profile Orders History
        Route::get('/orders', [CheckoutController::class, 'orders'])->name('customer.orders');

        // ----------------------------------------------------
        // ADMINISTRATIVE PORTAL (Restricted to Staff Roles)
        // ----------------------------------------------------
        Route::prefix('admin')->group(function () {
            // Dashboard Home page
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

            // Admin Role: Product & Variations CRUD
            Route::get('/products', [AdminController::class, 'productList'])->name('admin.products');
            Route::get('/products/create', [AdminController::class, 'productCreate'])->name('admin.products.create');
            Route::post('/products', [AdminController::class, 'productStore'])->name('admin.products.store');
            Route::get('/products/edit/{id}', [AdminController::class, 'productEdit'])->name('admin.products.edit');
            Route::post('/products/update/{id}', [AdminController::class, 'productUpdate'])->name('admin.products.update');
            Route::post('/products/delete/{id}', [AdminController::class, 'productDestroy'])->name('admin.products.delete');

            // Variations CRUD
            Route::post('/products/{productId}/variations', [AdminController::class, 'variationStore'])->name('admin.variations.store');
            Route::post('/variations/delete/{id}', [AdminController::class, 'variationDestroy'])->name('admin.variations.delete');

            // Categories CRUD
            Route::post('/categories', [AdminController::class, 'categoryStore'])->name('admin.categories.store');
            Route::post('/categories/update/{id}', [AdminController::class, 'categoryUpdate'])->name('admin.categories.update');
            Route::post('/categories/delete/{id}', [AdminController::class, 'categoryDestroy'])->name('admin.categories.delete');

            // Outdoor Sales Agent: Log Sales
            Route::post('/sales/outdoor', [AdminController::class, 'logOutdoorSale'])->name('admin.sales.outdoor');

            // Purchaser Role: Inventory & Restocking
            Route::get('/inventory', [AdminController::class, 'inventoryList'])->name('admin.inventory');
            Route::post('/inventory/restock', [AdminController::class, 'restock'])->name('admin.inventory.restock');

            // Storekeeper Role: Order Fulfillment
            Route::get('/orders', [AdminController::class, 'orderList'])->name('admin.orders');
            Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
            Route::post('/orders/{id}/shipping', [AdminController::class, 'updateShipping'])->name('admin.orders.shipping');
            Route::post('/orders/{id}/book-courier', [AdminController::class, 'bookCourier'])->name('admin.orders.book_courier');

            // Reports View
            Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
        });
    });
});
