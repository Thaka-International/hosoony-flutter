<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeekdaySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'schedule',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'schedule' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the classes using this schedule.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'weekday_schedule_id');
    }

    /**
     * Scope for active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default schedule.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get schedule for a specific day.
     */
    public function getDaySchedule(string $day): ?array
    {
        return $this->schedule[$day] ?? null;
    }

    /**
     * Get all available days.
     */
    public function getAvailableDays(): array
    {
        return array_keys($this->schedule ?? []);
    }

    /**
     * Check if a day is scheduled.
     */
    public function hasDay(string $day): bool
    {
        return isset($this->schedule[$day]);
    }

    /**
     * Get formatted schedule for display.
     */
    public function getFormattedSchedule(): array
    {
        $days = [
            'sunday' => 'الأحد',
            'monday' => 'الاثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            'saturday' => 'السبت',
        ];

        $formatted = [];
        foreach ($this->schedule as $day => $times) {
            $formatted[$days[$day] ?? $day] = $times;
        }

        return $formatted;
    }
}