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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->integer('score')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('grade', ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'])->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
            $table->index(['score', 'completed_at']);
            $table->unique(['exam_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
