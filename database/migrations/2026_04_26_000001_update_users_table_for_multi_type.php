<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Update users table.
     *
     * Add columns for multi-user-type support (super_admin, seller, tenant_owner)
     *
     * NOTE: This is an UPDATE migration. Run this AFTER Breeze creates default users table.
     * If you've already migrated, this adds the new columns.
     */
    public function up(): void
    {
        // Add new columns
        Schema::table('users', function (Blueprint $table) {
            // User type (super_admin, seller, tenant_owner)
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['super_admin', 'seller', 'tenant_owner'])
                    ->default('tenant_owner')
                    ->after('email');
            }

            // Status
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'suspended', 'pending', 'banned'])
                    ->default('active')
                    ->after('user_type');
            }

            // Phone
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }

            // Avatar
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 500)->nullable()->after('phone');
            }

            // Last login tracking
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
                $table->string('last_login_ip', 45)->nullable();
            }

            // Soft deletes
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Indexes
            $table->index('user_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_type',
                'status',
                'phone',
                'avatar',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
