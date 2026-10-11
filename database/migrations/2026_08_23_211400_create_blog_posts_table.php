<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug', 255)->unique();
            $table->string('title', 200);
            $table->string('excerpt', 500);
            $table->string('cover_image_url', 500)->nullable();
            $table->enum('cover_type', ['image', 'video', 'youtube'])->default('image');
            $table->string('cover_video_url', 500)->nullable();
            $table->string('cover_youtube_url', 500)->nullable();
            $table->json('tags');
            $table->date('published_at');
            $table->unsignedTinyInteger('read_time_minutes');
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
