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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'midterm', 'final', 'oral', 'written']);
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->nullable();
            $table->integer('total_points')->default(100);
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['class_id', 'status']);
            $table->index(['scheduled_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
