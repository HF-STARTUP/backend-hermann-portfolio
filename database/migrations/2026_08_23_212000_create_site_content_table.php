<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une ligne par section éditable : profile|hero|skills|homeSections|pages|footer
        Schema::create('site_content', function (Blueprint $table) {
            $table->string('section', 30)->primary();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_content');
    }
};
