<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fees_plan_id',
        'billing_cycle',
        'amount',
        'start_date',
        'end_date',
        'next_billing_date',
        'is_active',
        'auto_renew',
        'billing_history',
        'last_payment_at',
        'cancelled_at',
        'cancellation_reason',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'date',
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'billing_history' => 'array',
        'last_payment_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feesPlan(): BelongsTo
    {
        return $this->belongsTo(FeesPlan::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active' && ($this->end_date === null || $this->end_date > now());
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->end_date !== null && $this->end_date <= now();
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    /**
     * Get days until next billing
     */
    public function daysUntilNextBilling(): int
    {
        if (!$this->next_billing_date) {
            return 0;
        }
        return max(0, now()->diffInDays($this->next_billing_date, false));
    }

    /**
     * Get days until expiration
     */
    public function daysUntilExpiration(): int
    {
        if (!$this->end_date) {
            return 999; // No expiration for continuous subscriptions
        }
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Calculate next billing date based on cycle
     */
    public function calculateNextBillingDate(): \Carbon\Carbon
    {
        $baseDate = $this->next_billing_date ?? now();
        
        return match ($this->billing_cycle) {
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'semester' => $baseDate->addMonths(6),
            'yearly' => $baseDate->addYear(),
            default => $baseDate->addMonth(),
        };
    }

    /**
     * Add billing record
     */
    public function addBillingRecord(array $billingData): void
    {
        $history = $this->billing_history ?? [];
        $history[] = array_merge($billingData, [
            'date' => now()->toDateString(),
            'amount' => $this->amount,
        ]);
        
        $this->update([
            'billing_history' => $history,
            'last_payment_at' => now(),
            'next_billing_date' => $this->calculateNextBillingDate(),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'is_active' => false,
            'auto_renew' => false,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope for subscriptions due for billing
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('is_active', true)
                    ->where('auto_renew', true)
                    ->where('next_billing_date', '<=', now());
    }
}
