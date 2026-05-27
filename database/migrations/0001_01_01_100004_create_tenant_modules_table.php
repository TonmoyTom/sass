<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Create tenant_modules table.
     *
     * Tracks which modules each tenant has activated.
     * Supports both subscription and lifetime (one-time) access.
     */
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('module_id')->constrained('modules');

            // Access control
            $table->enum('status', ['active', 'inactive', 'trial', 'expired', 'purchased'])
                ->default('active');
            $table->enum('access_type', ['subscription', 'lifetime'])
                ->default('subscription');

            // Timestamps
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Pricing
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'one_time'])->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // Indexes & constraints
            $table->unique(['tenant_id', 'module_id']);
            $table->index('status');
            $table->index('access_type');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
