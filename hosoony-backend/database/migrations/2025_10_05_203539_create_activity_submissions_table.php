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
        Schema::create('activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('submission_content')->nullable();
            $table->string('attachment_url')->nullable();
            $table->enum('status', ['submitted', 'graded', 'returned'])->default('submitted');
            $table->integer('grade')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'student_id']);
            $table->index(['status', 'submitted_at']);
            $table->unique(['activity_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_submissions');
    }
};
