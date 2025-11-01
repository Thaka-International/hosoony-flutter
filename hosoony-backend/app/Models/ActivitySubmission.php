<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'student_id',
        'submitted_at',
        'content',
        'status',
        'points_earned',
        'feedback',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'points_earned' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isSubmitted(): bool
    {
        return !is_null($this->submitted_at);
    }

    public function isGraded(): bool
    {
        return !is_null($this->points_earned);
    }
}