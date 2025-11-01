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
        Schema::create('weekly_task_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('week_start_date'); // تاريخ بداية الأسبوع
            $table->date('week_end_date'); // تاريخ نهاية الأسبوع
            $table->enum('day_of_week', ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->date('task_date'); // التاريخ الفعلي للمهمة
            $table->foreignId('class_task_assignment_id')->constrained('class_task_assignments')->onDelete('cascade');
            $table->text('task_details')->nullable(); // ⭐ التفاصيل (نص حر يتم حفظه)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // فهارس للبحث السريع (أسماء مختصرة لتجنب مشكلة طول الاسم في MySQL)
            $table->index(['class_id', 'week_start_date'], 'wts_class_week_idx');
            $table->index(['class_id', 'task_date', 'class_task_assignment_id'], 'wts_class_date_task_idx');
            $table->index(['class_id', 'day_of_week'], 'wts_class_day_idx');
            
            // منع تكرار نفس المهمة في نفس اليوم لنفس الفصل
            $table->unique(['class_id', 'task_date', 'class_task_assignment_id'], 'wts_unique_class_date_task');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_task_schedules');
    }
};

