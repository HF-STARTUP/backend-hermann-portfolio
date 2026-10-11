<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tech_category_id')->constrained(table: 'tech_categories')->restrictOnDelete();
            $table->string('name', 100)->unique();
            $table->string('image_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
