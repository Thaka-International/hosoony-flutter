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
        Schema::create('gamification_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('source_type', ['daily_log', 'activity', 'exam', 'attendance', 'bonus', 'other']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('points');
            $table->text('description')->nullable();
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->index(['student_id', 'awarded_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_points');
    }
};
