<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'title',
        'description',
        'type',
        'duration_minutes',
        'order',
        'content',
        'notes',
        'status',
    ];

    /**
     * Get the session that owns the session item.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
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
        return $this->type === 'hifz';
    }

    /**
     * Check if item is murajaah type.
     */
    public function isMurajaah(): bool
    {
        return $this->type === 'murajaah';
    }

    /**
     * Check if item is tilawah type.
     */
    public function isTilawah(): bool
    {
        return $this->type === 'tilawah';
    }

    /**
     * Check if item is tajweed type.
     */
    public function isTajweed(): bool
    {
        return $this->type === 'tajweed';
    }

    /**
     * Check if item is tafseer type.
     */
    public function isTafseer(): bool
    {
        return $this->type === 'tafseer';
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'غير محدد';
        }

        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $hours . ' ساعة ' . $minutes . ' دقيقة';
        }

        return $minutes . ' دقيقة';
    }

    /**
     * Scope for items by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
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
        return $query->orderBy('order');
    }
}
