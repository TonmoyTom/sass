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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('bio')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('nt_id')->nullable();
            $table->string('tx_id')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('lnkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};
