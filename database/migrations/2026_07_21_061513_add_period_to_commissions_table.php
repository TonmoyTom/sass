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
        Schema::table('commissions', function (Blueprint $table) {
            $table->string('period', 7)->nullable()->after('commission_type'); // "2026-07"
            $table->unique(['sale_id', 'commission_type', 'period'], 'commissions_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropUnique('commissions_period_unique');
            $table->dropColumn('period');
        });
    }
};
