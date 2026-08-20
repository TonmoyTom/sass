<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('preview_video_source', ['youtube', 'upload'])->nullable()->after('thumbnail');
            $table->string('preview_video_url')->nullable()->after('preview_video_source');
            $table->string('preview_video_path')->nullable()->after('preview_video_url');
            $table->string('preview_image')->nullable()->after('preview_video_path');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['preview_video_source', 'preview_video_url', 'preview_video_path' , 'preview_image']);
        });
    }
};
