<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'student_id',
        'teacher_id',
        'class_id',
        'recitation_score',
        'pronunciation_score',
        'memorization_score',
        'understanding_score',
        'participation_score',
        'total_score',
        'recommendations',
        'student_feedback',
        'improvement_areas',
        'status',
        'evaluated_at',
    ];

    protected $casts = [
        'recitation_score' => 'decimal:1',
        'pronunciation_score' => 'decimal:1',
        'memorization_score' => 'decimal:1',
        'understanding_score' => 'decimal:1',
        'participation_score' => 'decimal:1',
        'total_score' => 'decimal:1',
        'evaluated_at' => 'datetime',
    ];

    /**
     * Get the session that owns the evaluation.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the student that owns the evaluation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher that owns the evaluation.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class that owns the evaluation.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Calculate total score automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            $evaluation->total_score = (
                $evaluation->recitation_score +
                $evaluation->pronunciation_score +
                $evaluation->memorization_score +
                $evaluation->understanding_score +
                $evaluation->participation_score
            ) / 5;
        });
    }

    /**
     * Get performance level based on total score.
     */
    public function getPerformanceLevelAttribute(): string
    {
        return match (true) {
            $this->total_score >= 9 => 'ممتاز',
            $this->total_score >= 8 => 'جيد جداً',
            $this->total_score >= 7 => 'جيد',
            $this->total_score >= 6 => 'مقبول',
            default => 'ضعيف',
        };
    }

    /**
     * Get performance level color.
     */
    public function getPerformanceColorAttribute(): string
    {
        return match (true) {
            $this->total_score >= 9 => 'success',
            $this->total_score >= 8 => 'info',
            $this->total_score >= 7 => 'warning',
            $this->total_score >= 6 => 'gray',
            default => 'danger',
        };
    }

    /**
     * Check if evaluation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if evaluation is reviewed.
     */
    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }

    /**
     * Mark evaluation as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'evaluated_at' => now(),
        ]);
    }

    /**
     * Mark evaluation as reviewed.
     */
    public function markAsReviewed(): void
    {
        $this->update([
            'status' => 'reviewed',
        ]);
    }

    /**
     * Get detailed score breakdown.
     */
    public function getScoreBreakdown(): array
    {
        return [
            'recitation' => [
                'score' => $this->recitation_score,
                'label' => 'التلاوة',
                'description' => 'دقة التلاوة واتباع أحكام التجويد',
            ],
            'pronunciation' => [
                'score' => $this->pronunciation_score,
                'label' => 'النطق',
                'description' => 'صحة النطق ووضوح الكلمات',
            ],
            'memorization' => [
                'score' => $this->memorization_score,
                'label' => 'الحفظ',
                'description' => 'قوة الحفظ وعدم النسيان',
            ],
            'understanding' => [
                'score' => $this->understanding_score,
                'label' => 'الفهم',
                'description' => 'فهم المعاني والمفاهيم',
            ],
            'participation' => [
                'score' => $this->participation_score,
                'label' => 'المشاركة',
                'description' => 'نشاط المشاركة والتفاعل',
            ],
        ];
    }

    /**
     * Scope for completed evaluations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for evaluations by teacher.
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for evaluations by student.
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope for evaluations by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for evaluations in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('evaluated_at', [$startDate, $endDate]);
    }
}