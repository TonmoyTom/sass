<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('module_id')->constrained('modules');
            $table->foreignId('module_tier_id')->nullable()->constrained('module_tiers')->nullOnDelete();

            // access control
            $table->enum('status', ['active', 'inactive', 'trial', 'expired', 'purchased'])->default('active');
            $table->enum('access_type', ['subscription', 'lifetime'])->default('subscription');

            // limits snapshot (tier-er limit purchase-er somoy)
            $table->json('limits')->nullable();

            // timestamps
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();   // null = lifetime

            // pricing
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'one_time'])->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'module_id']);
            $table->index('status');
            $table->index('access_type');

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
