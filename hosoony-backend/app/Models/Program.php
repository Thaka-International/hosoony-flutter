<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'duration_months',
        'price',
        'currency',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the classes for the program.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'program_id');
    }

    /**
     * Get active classes for the program.
     */
    public function activeClasses(): HasMany
    {
        return $this->classes()->where('status', 'active');
    }

    /**
     * Get male classes for the program.
     */
    public function maleClasses(): HasMany
    {
        return $this->classes()->where('gender', 'male');
    }

    /**
     * Get female classes for the program.
     */
    public function femaleClasses(): HasMany
    {
        return $this->classes()->where('gender', 'female');
    }

    /**
     * Check if program is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get total students count across all classes.
     */
    public function getTotalStudentsAttribute(): int
    {
        return $this->classes()->sum('current_students');
    }
}
