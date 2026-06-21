<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('token', 64)->unique();
            $table->string('email');            // kon owner login korbe
            $table->timestamp('expires_at');    // short expiry (2 min)
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index('token');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_login_tokens');
    }
};
