<?php

namespace App\Models;

use App\Models\Scopes\GenderSeparationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Get the class that owns the session.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the teacher that owns the session.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the session items for the session.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SessionItem::class, 'session_id');
    }

    /**
     * Get completed session items.
     */
    public function completedItems(): HasMany
    {
        return $this->items()->where('status', 'completed');
    }

    /**
     * Get pending session items.
     */
    public function pendingItems(): HasMany
    {
        return $this->items()->where('status', 'pending');
    }

    /**
     * Check if session is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if session is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if session is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if session is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if session is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at > now();
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }

        return $this->starts_at->diffInMinutes($this->ends_at);
    }

    /**
     * Scope for upcoming sessions.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    /**
     * Scope for sessions by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for sessions by teacher.
     */
    public function scopeByTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
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
