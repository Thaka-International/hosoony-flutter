<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'currency',
        'billing_period',
        'duration_months',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
