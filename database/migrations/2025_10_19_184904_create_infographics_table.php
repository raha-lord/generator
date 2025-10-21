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
        Schema::create('infographics', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('format')->default('png');
            $table->integer('file_size')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infographics');
    }
};
