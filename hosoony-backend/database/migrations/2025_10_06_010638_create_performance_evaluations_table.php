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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sessions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            
            // التقييم الأساسي
            $table->decimal('recitation_score', 3, 1)->comment('درجة التلاوة من 10');
            $table->decimal('pronunciation_score', 3, 1)->comment('درجة النطق من 10');
            $table->decimal('memorization_score', 3, 1)->comment('درجة الحفظ من 10');
            $table->decimal('understanding_score', 3, 1)->comment('درجة الفهم من 10');
            $table->decimal('participation_score', 3, 1)->comment('درجة المشاركة من 10');
            
            // الدرجة الإجمالية
            $table->decimal('total_score', 3, 1)->comment('الدرجة الإجمالية من 10');
            
            // التوصيات والتعليقات
            $table->text('recommendations')->nullable()->comment('التوصيات للمعلمة');
            $table->text('student_feedback')->nullable()->comment('ملاحظات للطالبة');
            $table->text('improvement_areas')->nullable()->comment('مجالات التحسين');
            
            // حالة التقييم
            $table->enum('status', ['draft', 'completed', 'reviewed'])->default('draft');
            $table->timestamp('evaluated_at')->nullable();
            
            $table->timestamps();

            // الفهارس
            $table->index(['session_id', 'student_id']);
            $table->index(['teacher_id', 'evaluated_at']);
            $table->index(['class_id', 'evaluated_at']);
            $table->index(['student_id', 'evaluated_at']);
            
            // ضمان تقييم واحد لكل طالبة في كل جلسة
            $table->unique(['session_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};