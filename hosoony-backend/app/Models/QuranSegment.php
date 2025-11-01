<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuranSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'start_ayah',
        'end_ayah',
        'start_page',
        'end_page',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dailyLogItems()
    {
        return $this->hasMany(DailyLogItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
