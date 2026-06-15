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
        Schema::create('module_tiers', function (Blueprint $table) {
           $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('name', 100);              // Basic, Standard, Pro
            $table->string('slug', 50);               // basic, standard, pro

            // limits — { "max_students": 100, "max_teachers": 10 }
            $table->json('limits')->nullable();

            // pricing
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->decimal('one_time_price', 10, 2)->nullable(); // addon tier hole

            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);  // "Most popular" badge
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // ek module-e ek tier slug ekbar
            $table->unique(['module_id', 'slug']);
            $table->index('module_id');
            $table->index('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_tiers');
    }
};
