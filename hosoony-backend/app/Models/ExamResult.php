<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'score',
        'total_points',
        'percentage',
        'grade',
        'submitted_at',
        'feedback',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_points' => 'decimal:2',
        'percentage' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isPassed(): bool
    {
        return $this->percentage >= 60;
    }

    public function getGradeAttribute(): string
    {
        if ($this->percentage >= 90) return 'A+';
        if ($this->percentage >= 80) return 'A';
        if ($this->percentage >= 70) return 'B+';
        if ($this->percentage >= 60) return 'B';
        if ($this->percentage >= 50) return 'C+';
        if ($this->percentage >= 40) return 'C';
        return 'F';
    }
}