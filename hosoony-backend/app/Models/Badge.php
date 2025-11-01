<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'points_required',
        'is_active',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'is_active' => 'boolean',
    ];

    public function studentBadges(): HasMany
    {
        return $this->hasMany(StudentBadge::class);
    }
}