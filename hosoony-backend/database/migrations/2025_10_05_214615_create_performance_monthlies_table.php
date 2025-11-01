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
        Schema::create('performance_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('total_points')->default(0);
            $table->integer('rank')->nullable();
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'year', 'month']);
            $table->index(['year', 'month']);
            $table->unique(['student_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_monthlies');
    }
};