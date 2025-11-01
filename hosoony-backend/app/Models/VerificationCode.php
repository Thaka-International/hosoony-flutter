<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'code',
        'type',
        'is_used',
        'expires_at',
        'used_at',
        'whatsapp_message_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected $attributes = [
        'is_used' => false,
    ];

    /**
     * Generate a random 6-digit verification code.
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification code for phone number.
     */
    public static function createForPhone(string $phoneNumber, string $type = 'login'): self
    {
        // Invalidate any existing unused codes for this phone
        static::where('phone_number', $phoneNumber)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        return static::create([
            'phone_number' => $phoneNumber,
            'code' => static::generateCode(),
            'type' => $type,
            'expires_at' => now()->addMinutes(5), // Code expires in 5 minutes
        ]);
    }

    /**
     * Verify a code for a phone number.
     */
    public static function verifyCode(string $phoneNumber, string $code): ?self
    {
        $verificationCode = static::where('phone_number', $phoneNumber)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($verificationCode) {
            $verificationCode->update([
                'is_used' => true,
                'used_at' => now(),
            ]);
        }

        return $verificationCode;
    }

    /**
     * Check if code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope for active (unused and not expired) codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for specific phone number.
     */
    public function scopeForPhone($query, string $phoneNumber)
    {
        return $query->where('phone_number', $phoneNumber);
    }
}