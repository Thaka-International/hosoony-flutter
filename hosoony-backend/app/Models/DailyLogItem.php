<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_log_id',
        'task_definition_id',
        'quran_segment_id',
        'quantity',
        'duration_minutes',
        'notes',
        'status',
        'proof_type',
    ];

    /**
     * Get the daily log that owns the item.
     */
    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    /**
     * Get the task definition that owns the item.
     */
    public function taskDefinition(): BelongsTo
    {
        return $this->belongsTo(DailyTaskDefinition::class);
    }

    /**
     * Get the Quran segment for the item.
     */
    public function quranSegment(): BelongsTo
    {
        return $this->belongsTo(QuranSegment::class);
    }

    /**
     * Check if item is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if item is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if item is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if item is skipped.
     */
    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }

    /**
     * Check if item is hifz type.
     */
    public function isHifz(): bool
    {
        return $this->taskDefinition?->type === 'hifz';
    }

    /**
     * Check if item is murajaah type.
     */
    public function isMurajaah(): bool
    {
        return $this->taskDefinition?->type === 'murajaah';
    }

    /**
     * Check if item is tilawah type.
     */
    public function isTilawah(): bool
    {
        return $this->taskDefinition?->type === 'tilawah';
    }

    /**
     * Check if item is tajweed type.
     */
    public function isTajweed(): bool
    {
        return $this->taskDefinition?->type === 'tajweed';
    }

    /**
     * Check if item is tafseer type.
     */
    public function isTafseer(): bool
    {
        return $this->taskDefinition?->type === 'tafseer';
    }

    /**
     * Get formatted duration attribute.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }

    /**
     * Scope for items by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->whereHas('taskDefinition', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Scope for items by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for ordered items.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}