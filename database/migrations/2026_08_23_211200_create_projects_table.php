<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_category_id')->constrained(table: 'project_categories')->restrictOnDelete();
            $table->string('slug', 255)->unique();
            $table->string('title', 150);
            $table->string('role', 120);
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->json('tech_stack');
            $table->string('demo_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->boolean('featured')->default(false);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
