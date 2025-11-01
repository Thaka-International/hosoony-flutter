<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanionsPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'target_date',
        'grouping',
        'algorithm',
        'attendance_source',
        'locked_pairs',
        'pairings',
        'room_assignments',
        'zoom_url_snapshot',
        'zoom_password_snapshot',
        'published_at',
        'published_by',
        'auto_published',
    ];

    protected $casts = [
        'target_date' => 'date',
        'locked_pairs' => 'array',
        'pairings' => 'array',
        'room_assignments' => 'array',
        'published_at' => 'datetime',
        'auto_published' => 'boolean',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function isPublished(): bool
    {
        return !is_null($this->published_at);
    }

    public function isAutoPublished(): bool
    {
        return $this->auto_published;
    }
}