<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTaskSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'week_start_date',
        'week_end_date',
        'day_of_week',
        'task_date',
        'class_task_assignment_id',
        'task_details',
        'created_by',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'task_date' => 'date',
    ];

    /**
     * Get the class that owns the weekly task schedule.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the class task assignment.
     */
    public function classTaskAssignment(): BelongsTo
    {
        return $this->belongsTo(ClassTaskAssignment::class, 'class_task_assignment_id');
    }

    /**
     * Get the user who created the schedule.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for specific class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('task_date', $date);
    }

    /**
     * Scope for specific day of week.
     */
    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope: جلب التفاصيل لمهمة في يوم محدد
     */
    public function scopeForTaskOnDate($query, $classId, $taskAssignmentId, $date)
    {
        return $query->where('class_id', $classId)
            ->where('class_task_assignment_id', $taskAssignmentId)
            ->where('task_date', $date);
    }

    /**
     * Scope for a specific week.
     */
    public function scopeForWeek($query, $startDate, $endDate)
    {
        return $query->where('week_start_date', $startDate)
            ->where('week_end_date', $endDate);
    }

    /**
     * Get formatted day name in Arabic.
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            'saturday' => 'السبت',
        ];

        return $days[$this->day_of_week] ?? $this->day_of_week;
    }
}


