<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 20)->unique();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->decimal('commission_rate', 5, 2)->default(70.00);
            $table->integer('total_sales')->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bkash_number', 20)->nullable();
            $table->string('nid_number', 20)->nullable();
            $table->boolean('nid_verified')->default(false);
            $table->timestamp('joined_at');
            $table->index('referral_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Seller', function (Blueprint $table) {
            //
        });
    }
};
