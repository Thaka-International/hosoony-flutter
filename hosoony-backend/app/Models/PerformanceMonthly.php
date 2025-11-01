<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMonthly extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'year',
        'month',
        'total_points',
        'rank',
        'attendance_percentage',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_points' => 'integer',
        'rank' => 'integer',
        'attendance_percentage' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
