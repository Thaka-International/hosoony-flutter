<?php

namespace App\Models;

use App\Models\Scopes\GenderSeparationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'class_id',
        'day_of_week',
        'start_time',
        'end_time',
        'zoom_link',
        'zoom_meeting_id',
        'zoom_password',
        'notes',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the class that owns the schedule.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Check if schedule is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Get formatted time range.
     */
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    /**
     * Scope for active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific day of week.
     */
    public function scopeOnDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Temporarily disabled to fix memory issues
        // static::addGlobalScope(new GenderSeparationScope());
    }
}
