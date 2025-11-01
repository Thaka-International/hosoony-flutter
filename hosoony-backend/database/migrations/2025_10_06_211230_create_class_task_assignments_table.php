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
        Schema::create('class_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('daily_task_definition_id')->constrained('daily_task_definitions')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // ترتيب المهمة في الفصل
            $table->timestamps();

            $table->unique(['class_id', 'daily_task_definition_id']);
            $table->index(['class_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_task_assignments');
    }
};