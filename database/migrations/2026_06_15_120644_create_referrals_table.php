<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->string('tenant_id', 36)->nullable();
            $table->string('referral_code', 20);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('landing_url', 500)->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index('referral_code');
            $table->index('tenant_id');
            $table->index('seller_id');

            // tenant_id FK — tenants table-er id string (uuid), tai nullable FK
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
