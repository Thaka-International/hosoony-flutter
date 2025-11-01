<?php

namespace App\Models;

use App\Models\Scopes\GenderSeparationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'weekday_schedule_id',
        'name',
        'description',
        'gender',
        'max_students',
        'current_students',
        'status',
        'start_date',
        'end_date',
        'zoom_url',
        'zoom_password',
        'zoom_room_start',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the program that owns the class.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the schedules for the class.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    /**
     * Get the sessions for the class.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'class_id');
    }

    /**
     * Get the students in the class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    /**
     * Get the teachers in the class.
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->whereIn('role', ['teacher', 'teacher_support']);
    }

    /**
     * Get the companions publications for the class.
     */
    public function companionsPublications(): HasMany
    {
        return $this->hasMany(CompanionsPublication::class, 'class_id');
    }

    /**
     * Get active schedules for the class.
     */
    public function activeSchedules(): HasMany
    {
        return $this->schedules()->where('is_active', true);
    }

    /**
     * Get upcoming sessions for the class.
     */
    public function upcomingSessions(): HasMany
    {
        return $this->sessions()->where('starts_at', '>', now());
    }

    /**
     * Get the weekday schedule for the class.
     */
    public function weekdaySchedule(): BelongsTo
    {
        return $this->belongsTo(WeekdaySchedule::class, 'weekday_schedule_id');
    }

    /**
     * Get the task assignments for the class.
     */
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(ClassTaskAssignment::class, 'class_id');
    }

    /**
     * Get the active task assignments for the class.
     */
    public function activeTaskAssignments(): HasMany
    {
        return $this->taskAssignments()->where('is_active', true)->orderBy('order');
    }

    /**
     * Get the weekly task schedules for the class.
     */
    public function weeklyTaskSchedules(): HasMany
    {
        return $this->hasMany(WeeklyTaskSchedule::class, 'class_id');
    }

    /**
     * Check if class is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if class has available spots.
     */
    public function hasAvailableSpots(): bool
    {
        return $this->current_students < $this->max_students;
    }

    /**
     * Get available spots count.
     */
    public function getAvailableSpotsAttribute(): int
    {
        return $this->max_students - $this->current_students;
    }

    /**
     * Check if class is for males.
     */
    public function isMale(): bool
    {
        return $this->gender === 'male';
    }

    /**
     * Check if class is for females.
     */
    public function isFemale(): bool
    {
        return $this->gender === 'female';
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
