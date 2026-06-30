<?php

declare(strict_types=1);

use App\Events\NotificationSent;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\WithdrawRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\CommissionController;
use App\Http\Controllers\Seller\ModuleRequestController;
use App\Http\Controllers\Seller\ReferralController;
use App\Http\Controllers\Seller\WalletController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\CheckoutController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes (Central System)
|--------------------------------------------------------------------------
|
| All routes for the central platform (yourplatform.com).
| Tenant routes are in routes/tenant.php (subdomain-based).
|
| Route organization:

|
*/

// ─────────────────────────────────────────────
// 1. PUBLIC ROUTES (No Authentication Required)
// ─────────────────────────────────────────────

Route::get('/', function () {

    return Inertia::render('Welcome');
})->name('home');

Route::get('/pricing', function () {
    return Inertia::render('Public/Pricing');
})->name('pricing');

Route::get('/features', function () {
    return Inertia::render('Public/Features');
})->name('features');
Route::get('/about', function () {
    return Inertia::render('Public/About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Public/Contact');
})->name('contact');

Route::get('/become-seller', function () {
    return Inertia::render('Public/BecomeSeller');
})->name('become-seller');

Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->middleware('auth')->name('impersonate.stop');

// ─────────────────────────────────────────────
// 2. AUTHENTICATION ROUTES
// ─────────────────────────────────────────────
// Login, register, password reset (handled by Breeze)

require __DIR__.'/auth.php';

// ─────────────────────────────────────────────
// 3. AUTHENTICATED USER ROUTES (Common)
// ─────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/auth-test', function () {
        return response()->json([
            'user' => auth()->user(),
            'session_id' => session()->getId(),
        ]);
    });
    Route::get('/test-notification', function () {
        NotificationSent::dispatch(
            'এটি একটি test notification',
            auth()->id(),
            'info'
        );

        return 'Notification sent!';
    });

    // Profile management (for any logged-in user)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::patch('/profile/address', [ProfileController::class, 'updateAddress'])
        ->name('profile.address.update');
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])
        ->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
    // ✅ Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Generic dashboard - redirects based on user type
    Route::get('/dashboard', function () {
        return redirect()->to(auth()->user()->getDashboardUrl());
    })->name('dashboard');
});

// ─────────────────────────────────────────────
// 4. SUPER ADMIN ROUTES (/admin/*)
// ─────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
        // Tenants management
        Route::resource('tenants', TenantController::class);
        Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenants/{tenant}/reactivate', [TenantController::class, 'reactivate'])->name('tenants.reactivate');
        Route::post('/tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');

        // // Sellers management
        Route::resource('sellers', SellerController::class);
        Route::post('/sellers/{seller}/approve', [SellerController::class, 'approve'])
            ->name('sellers.approve');
        Route::post('/sellers/{seller}/reject', [SellerController::class, 'reject'])
            ->name('sellers.reject');
        Route::post('/sellers/{seller}/suspend', [SellerController::class, 'suspend'])
            ->name('sellers.suspend');

        Route::get('/module-requests/create', [App\Http\Controllers\Admin\ModuleRequestController::class, 'create'])->name('module-requests.create');
        Route::post('/module-requests', [App\Http\Controllers\Admin\ModuleRequestController::class, 'store'])->name('module-requests.store');
        Route::get('/module-requests', [App\Http\Controllers\Admin\ModuleRequestController::class, 'index'])->name('module-requests.index');
        Route::get('/module-requests/{moduleRequest}', [App\Http\Controllers\Admin\ModuleRequestController::class, 'show'])->name('module-requests.show');
        Route::post('/module-requests/{moduleRequest}/approve', [App\Http\Controllers\Admin\ModuleRequestController::class, 'approve'])->name('module-requests.approve');
        Route::post('/module-requests/{moduleRequest}/reject', [App\Http\Controllers\Admin\ModuleRequestController::class, 'reject'])->name('module-requests.reject');
        Route::patch('/module-requests/{moduleRequest}/status', [App\Http\Controllers\Admin\ModuleRequestController::class, 'updateStatus'])->name('module-requests.update-status');
        Route::patch('/module-requests/{moduleRequest}/note', [App\Http\Controllers\Admin\ModuleRequestController::class, 'updateNote'])->name('module-requests.update-note');

        // // Modules management
        Route::resource('modules', ModuleController::class);
        // commissions
        Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{commission}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('commissions.approve');
        Route::post('/commissions/{commission}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('commissions.reject');
        Route::post('/commissions/approve-due', [App\Http\Controllers\Admin\CommissionController::class, 'approveDue'])->name('commissions.approve-due');
        //  Orders & Invoices
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

        Route::get('/withdraw-requests', [WithdrawRequestController::class, 'index'])
            ->name('withdraw.index');
        Route::get('/withdraw-requests/{withdraw}', [WithdrawRequestController::class, 'show'])
            ->name('withdraw.show');
        Route::post('/withdraw-requests/{withdraw}/approve', [WithdrawRequestController::class, 'approve'])
            ->name('withdraw.approve');
        Route::post('/withdraw-requests/{withdraw}/reject', [WithdrawRequestController::class, 'reject'])
            ->name('withdraw.reject');

        // // Reports & Analytics
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/tenants', [ReportController::class, 'tenants'])->name('reports.tenants');
        Route::get('/reports/sellers', [ReportController::class, 'sellers'])->name('reports.sellers');

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });

        Route::post('/users/{user}/impersonate', [ImpersonateController::class, 'start'])->name('users.impersonate');

        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StaffController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\StaffController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\StaffController::class, 'store'])->name('store');
            Route::get('/{staff}/edit', [App\Http\Controllers\Admin\StaffController::class, 'edit'])->name('edit');
            Route::put('/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'update'])->name('update');
            Route::delete('/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('destroy');
        });

        // // Settings
        // Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
        //     ->name('settings.index');
        // Route::patch('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
        //     ->name('settings.update');
    });

// ─────────────────────────────────────────────
// 5. SELLER ROUTES (/seller/*)
// ─────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Seller\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/modules', [ModuleRequestController::class, 'index'])->name('modules.index');
        Route::post('/modules/request', [ModuleRequestController::class, 'store'])->name('modules.request');
        Route::get('/modules/{moduleRequest}', [ModuleRequestController::class, 'show'])->name('modules.show');

        // // Referrals
        Route::get('/referrals', [ReferralController::class, 'index'])
            ->name('referrals.index');

        // // Commissions
        Route::get('/commissions', [CommissionController::class, 'index'])
            ->name('commissions.index');

        // // Wallet
        Route::get('/wallet', [WalletController::class, 'index'])
            ->name('wallet.index');
        Route::get('/wallet/withdraw', [WalletController::class, 'withdrawPage'])
            ->name('wallet.withdraw.page');
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])
            ->name('wallet.withdraw');
        Route::post('/wallet/payout-details', [WalletController::class, 'updatePayout'])
            ->name('wallet.payout.update');
        Route::get('/wallet/withdraw/history', [WalletController::class, 'withdrawHistory'])
            ->name('wallet.withdraw.history');
        Route::get('/wallet/withdraw/{withdraw}', [WalletController::class, 'withdrawShow'])
            ->name('wallet.withdraw.show');

    });

// ─────────────────────────────────────────────
// 6. TENANT OWNER ROUTES (/tenant/*)
// ─────────────────────────────────────────────
// These are for tenant_owners to manage their school subscription
// (Different from tenant.php which handles subdomain-based school routes)

Route::middleware(['auth', 'verified', 'tenant_owner'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {

        Route::get('/dashboard', [App\Http\Controllers\Tenant\DashboardController::class, 'index'])
            ->name('central.dashboard');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::get('/modules', [App\Http\Controllers\Tenant\ModuleController::class, 'index'])->name('modules.index');
        Route::get('/workspace/open', [App\Http\Controllers\Tenant\ModuleController::class, 'openWorkspace'])->name('workspace.open');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

        Route::get('/orders', [App\Http\Controllers\Tenant\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\Tenant\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [App\Http\Controllers\Tenant\OrderController::class, 'invoice'])->name('orders.invoice');

    });

// ─────────────────────────────────────────────
// 7. PAYMENT WEBHOOK ROUTES (No CSRF, Public)
// ─────────────────────────────────────────────

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Route::post('/sslcommerz/success', [\App\Http\Controllers\Webhooks\SSLCommerzController::class, 'success'])
    //     ->name('sslcommerz.success');
    // Route::post('/sslcommerz/fail', [\App\Http\Controllers\Webhooks\SSLCommerzController::class, 'fail'])
    //     ->name('sslcommerz.fail');
    // Route::post('/sslcommerz/cancel', [\App\Http\Controllers\Webhooks\SSLCommerzController::class, 'cancel'])
    //     ->name('sslcommerz.cancel');
    // Route::post('/sslcommerz/ipn', [\App\Http\Controllers\Webhooks\SSLCommerzController::class, 'ipn'])
    //     ->name('sslcommerz.ipn');
});
