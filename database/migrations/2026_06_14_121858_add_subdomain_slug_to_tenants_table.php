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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug', 100)->unique()->nullable()->after('name');
            $table->string('subdomain', 100)->unique()->nullable()->after('slug');
            $table->string('database_name', 100)->unique()->nullable()->after('subdomain');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['slug', 'subdomain', 'database_name']);
        });
    }
};
