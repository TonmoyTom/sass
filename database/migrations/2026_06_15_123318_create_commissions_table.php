<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('rate', 5, 2);                  // e.g. 70.00 (%)
            $table->enum('commission_type', ['one_time', 'recurring']);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('hold_until')->nullable();    // 30-day hold
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('seller_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
