<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('requires_completion')->default(false); // ei lesson complete na hole porerta lock

            // video (optional)
            $table->enum('video_source', ['youtube', 'upload'])->nullable();
            $table->string('video_url')->nullable();       // youtube link
            $table->string('video_path')->nullable();       // R2 uploaded path
            $table->unsignedInteger('video_duration_minutes')->nullable();

            // ebook (optional)
            $table->string('ebook_path')->nullable();
            $table->string('ebook_title')->nullable();

            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
