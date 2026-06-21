<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // ke add korlo (owner)
            $table->timestamps();

            $table->unique('tenant_id'); // ek tenant = ek cart
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
