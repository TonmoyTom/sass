<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Create tenants table.
     *
     * This is the central table that holds all tenant (school) records.
     * Each tenant gets their own database for full data isolation.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // Stancl/Tenancy uses string ID as identifier
            $table->string('id')->primary();

            // Custom columns
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspended_reason')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();

            // Stancl's data column (JSON for custom data)
            $table->json('data')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('owner_id');
            $table->index('referred_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
