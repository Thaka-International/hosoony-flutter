<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'type',
        'total_points',
        'duration_minutes',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function isFinished(): bool
    {
        return $this->end_date < now();
    }
}