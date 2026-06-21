<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('module_tier_id')->constrained('module_tiers')->cascadeOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('price', 10, 2);          // snapshot (tier price add korar somoy)
            $table->string('referral_code', 20)->nullable(); // seller code (commission-er jonno)
            $table->timestamps();

            // ek module ek-bar-i cart-e (duto tier na)
            $table->unique(['cart_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
