<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();

            // video tracking
            $table->unsignedInteger('video_watched_seconds')->default(0);
            $table->boolean('video_completed')->default(false);

            // ebook tracking
            $table->timestamp('ebook_opened_at')->nullable();

            // quiz tracking — best/latest passed attempt reference
            $table->boolean('quiz_passed')->nullable(); // null = quiz nai ei lesson-e

            $table->timestamps();

            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
