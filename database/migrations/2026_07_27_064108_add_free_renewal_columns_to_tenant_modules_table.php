<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->boolean('is_free_renewal')->default(false)->after('price_paid');
            $table->foreignId('free_renewed_by')->nullable()->after('is_free_renewal');
            $table->string('free_renewal_note')->nullable()->after('free_renewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->dropColumn(['is_free_renewal', 'free_renewed_by', 'free_renewal_note']);
        });
    }
};
