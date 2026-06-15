<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->foreignId('module_tier_id')->nullable()->constrained('module_tiers')->nullOnDelete();
            $table->enum('sale_type', ['subscription', 'module', 'addon']);
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('admin_amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->timestamp('sold_at');
            $table->timestamps();

            $table->index('seller_id');
            $table->index('status');
            $table->index('tenant_id');

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
