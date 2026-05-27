<?php

declare(strict_types=1);

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
| 1. Public routes (no auth)
| 2. Auth routes (login, register, etc.)
| 3. Super Admin routes (/admin/*)
| 4. Seller routes (/seller/*)
| 5. Tenant Owner routes (/tenant/*)
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

// ─────────────────────────────────────────────
// 2. AUTHENTICATION ROUTES
// ─────────────────────────────────────────────
// Login, register, password reset (handled by Breeze)

require __DIR__.'/auth.php';

// ─────────────────────────────────────────────
// 3. AUTHENTICATED USER ROUTES (Common)
// ─────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Profile management (for any logged-in user)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])
        ->name('profile.destroy');

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
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Tenants management
        // Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
        // Route::post('/tenants/{tenant}/suspend', [\App\Http\Controllers\Admin\TenantController::class, 'suspend'])
        //     ->name('tenants.suspend');
        // Route::post('/tenants/{tenant}/reactivate', [\App\Http\Controllers\Admin\TenantController::class, 'reactivate'])
        //     ->name('tenants.reactivate');
        // Route::post('/tenants/{tenant}/impersonate', [\App\Http\Controllers\Admin\TenantController::class, 'impersonate'])
        //     ->name('tenants.impersonate');

        // // Sellers management
        // Route::resource('sellers', \App\Http\Controllers\Admin\SellerController::class);
        // Route::post('/sellers/{seller}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approve'])
        //     ->name('sellers.approve');
        // Route::post('/sellers/{seller}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'reject'])
        //     ->name('sellers.reject');
        // Route::post('/sellers/{seller}/suspend', [\App\Http\Controllers\Admin\SellerController::class, 'suspend'])
        //     ->name('sellers.suspend');

        // // Modules management
        // Route::get('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])
        //     ->name('modules.index');
        // Route::get('/modules/{module}/edit', [\App\Http\Controllers\Admin\ModuleController::class, 'edit'])
        //     ->name('modules.edit');
        // Route::patch('/modules/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'update'])
        //     ->name('modules.update');
        // Route::post('/modules/sync', [\App\Http\Controllers\Admin\ModuleController::class, 'sync'])
        //     ->name('modules.sync');

        // // Subscription Plans
        // Route::resource('plans', \App\Http\Controllers\Admin\SubscriptionPlanController::class);

        // // Payments & Invoices
        // Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])
        //     ->name('payments.index');
        // Route::get('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])
        //     ->name('payments.show');
        // Route::post('/payments/{payment}/refund', [\App\Http\Controllers\Admin\PaymentController::class, 'refund'])
        //     ->name('payments.refund');

        // Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])
        //     ->name('invoices.index');
        // Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Admin\InvoiceController::class, 'download'])
        //     ->name('invoices.download');

        // // Withdrawal Requests
        // Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])
        //     ->name('withdrawals.index');
        // Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])
        //     ->name('withdrawals.approve');
        // Route::post('/withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])
        //     ->name('withdrawals.reject');

        // // Reports & Analytics
        // Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
        //     ->name('reports.index');
        // Route::get('/reports/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])
        //     ->name('reports.revenue');
        // Route::get('/reports/tenants', [\App\Http\Controllers\Admin\ReportController::class, 'tenants'])
        //     ->name('reports.tenants');
        // Route::get('/reports/sellers', [\App\Http\Controllers\Admin\ReportController::class, 'sellers'])
        //     ->name('reports.sellers');

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
        Route::get('/dashboard', [\App\Http\Controllers\Seller\DashboardController::class, 'index'])
            ->name('dashboard');

        // // Referrals
        // Route::get('/referrals', [\App\Http\Controllers\Seller\ReferralController::class, 'index'])
        //     ->name('referrals.index');

        // // Commissions
        // Route::get('/commissions', [\App\Http\Controllers\Seller\CommissionController::class, 'index'])
        //     ->name('commissions.index');

        // // Wallet
        // Route::get('/wallet', [\App\Http\Controllers\Seller\WalletController::class, 'index'])
        //     ->name('wallet.index');
        // Route::post('/wallet/withdraw', [\App\Http\Controllers\Seller\WalletController::class, 'withdraw'])
        //     ->name('wallet.withdraw');

        // // Profile
        // Route::get('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'edit'])
        //     ->name('profile.edit');
        // Route::patch('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'update'])
        //     ->name('profile.update');
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

        // // Tenant signup (create new school)
        // Route::get('/signup', [\App\Http\Controllers\Tenant\SignupController::class, 'show'])
        //     ->name('signup');
        // Route::post('/signup', [\App\Http\Controllers\Tenant\SignupController::class, 'store'])
        //     ->name('signup.store');

        // // My tenants list (if owner has multiple)
        // Route::get('/my-tenants', [\App\Http\Controllers\Tenant\OwnerController::class, 'index'])
        //     ->name('my-tenants');

        // // Subscription management
        // Route::get('/subscription', [\App\Http\Controllers\Tenant\SubscriptionController::class, 'show'])
        //     ->name('subscription.show');
        // Route::get('/subscription/upgrade', [\App\Http\Controllers\Tenant\SubscriptionController::class, 'upgrade'])
        //     ->name('subscription.upgrade');
        // Route::post('/subscription/upgrade', [\App\Http\Controllers\Tenant\SubscriptionController::class, 'processUpgrade'])
        //     ->name('subscription.process-upgrade');

        // // Billing
        // Route::get('/billing', [\App\Http\Controllers\Tenant\BillingController::class, 'index'])
        //     ->name('billing.index');
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
