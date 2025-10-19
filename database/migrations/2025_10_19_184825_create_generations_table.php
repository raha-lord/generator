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
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('generatable_type');
            $table->unsignedBigInteger('generatable_id');
            $table->string('status')->default('pending');
            $table->integer('cost')->default(0);
            $table->text('prompt');
            $table->text('result_path')->nullable();
            $table->string('public_url')->nullable()->unique();
            $table->boolean('is_public')->default(false);
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('moderation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('uuid');
            $table->index(['generatable_type', 'generatable_id']);
            $table->index('status');
            $table->index('public_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
