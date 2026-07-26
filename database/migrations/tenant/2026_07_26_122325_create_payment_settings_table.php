<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('method'); // bkash, nagad, bank
            $table->boolean('is_active')->default(false);
            $table->string('merchant_number')->nullable();   // bKash/Nagad merchant/personal number
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('branch')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
