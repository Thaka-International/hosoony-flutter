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
        Schema::create('daily_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_log_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_definition_id')->constrained('daily_task_definitions')->onDelete('cascade');
            $table->foreignId('quran_segment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['daily_log_id', 'task_definition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_log_items');
    }
};
