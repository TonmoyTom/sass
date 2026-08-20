<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            // module alias (e.g. 'lms', 'ecommerce') — matches
            // Tenant::enabledModules()'s output, not a local FK since the
            // actual Module record lives on the central connection.
            $table->string('module');
            $table->timestamps();

            $table->unique(['role_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_modules');
    }
};