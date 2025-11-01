<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassTaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'daily_task_definition_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the class that owns the assignment.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Alias for class() method for Filament compatibility.
     */
    public function classModel(): BelongsTo
    {
        return $this->class();
    }

    /**
     * Get the task definition that owns the assignment.
     */
    public function taskDefinition(): BelongsTo
    {
        return $this->belongsTo(DailyTaskDefinition::class, 'daily_task_definition_id');
    }

    /**
     * Scope for active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope ordered by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the weekly task schedules for this task assignment.
     */
    public function weeklyTaskSchedules(): HasMany
    {
        return $this->hasMany(WeeklyTaskSchedule::class, 'class_task_assignment_id');
    }
}