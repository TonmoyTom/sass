<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Create modules table.
     *
     * Registry of all modules available on the platform.
     * Supports both subscription and one-time purchase modules.
     * Auto-synced from Modules/<ModuleName>/module.json files.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('alias', 50)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('version', 20)->default('1.0.0');

            // Pricing nature (actual price tier-e)
            $table->enum('pricing_type', ['subscription', 'one_time'])->default('subscription');

            // Commission (module-level, seller's cut)
            $table->decimal('commission_rate', 5, 2)->default(70.00);

            // Categorization
            $table->enum('module_category', ['core', 'addon', 'utility'])->default('core');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->integer('sort_order')->default(0);

            // Metadata
            $table->json('features')->nullable();
            $table->json('dependencies')->nullable();

            $table->timestamps();

            $table->index('alias');
            $table->index('is_active');
            $table->index('pricing_type');
            $table->index('module_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
