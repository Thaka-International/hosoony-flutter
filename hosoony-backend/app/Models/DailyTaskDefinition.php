<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyTaskDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'task_location',
        'points_weight',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the daily log items for the task definition.
     */
    public function dailyLogItems(): HasMany
    {
        return $this->hasMany(DailyLogItem::class);
    }

    /**
     * Get the class assignments for the task definition.
     */
    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassTaskAssignment::class, 'daily_task_definition_id');
    }

    /**
     * Scope for active task definitions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific task type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}