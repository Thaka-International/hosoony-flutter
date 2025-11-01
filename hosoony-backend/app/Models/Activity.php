<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type', // daily_task, assignment, quiz, exam
        'points',
        'due_date',
        'is_daily', // true for daily activities
        'is_recurring', // true for recurring daily tasks
        'created_by', // admin, teacher, or teacher_support
        'status', // draft, published, completed, cancelled
        'instructions',
        'requirements',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_daily' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the classes that this activity is assigned to.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'activity_class_assignments', 'activity_id', 'class_id');
    }

    /**
     * Get the user who created this activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the submissions for this activity.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    /**
     * Get daily logs related to this activity.
     */
    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    /**
     * Check if activity is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'published';
    }

    /**
     * Check if activity is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if this is a daily activity.
     */
    public function isDaily(): bool
    {
        return $this->is_daily;
    }

    /**
     * Check if this is a recurring daily activity.
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Get students who can see this activity.
     */
    public function getAssignedStudents()
    {
        $students = collect();
        
        foreach ($this->classes as $class) {
            $students = $students->merge($class->students);
        }
        
        return $students->unique('id');
    }

    /**
     * Check if activity is assigned to a specific class.
     */
    public function isAssignedToClass($classId): bool
    {
        return $this->classes()->where('class_id', $classId)->exists();
    }

    /**
     * Check if activity is assigned to a specific student.
     */
    public function isAssignedToStudent($studentId): bool
    {
        $student = User::find($studentId);
        if (!$student || !$student->class_id) {
            return false;
        }
        
        return $this->isAssignedToClass($student->class_id);
    }
}