<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'target_type',
        'title',
        'message',
        'type',
        'channel',
        'data',
        'sent_at',
        'read_at',
        'status',
        'email_subject',
        'email_template',
        'sms_template',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is sent
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Get target users based on target_type
     */
    public function getTargetUsers()
    {
        switch ($this->target_type) {
            case 'user':
                return collect([$this->user]);
            case 'class':
                return $this->class ? $this->class->students : collect();
            case 'all_students':
                return User::where('role', 'student')->get();
            case 'all_teachers':
                return User::whereIn('role', ['teacher', 'teacher_support'])->get();
            case 'all_users':
                return User::all();
            default:
                return collect();
        }
    }

    /**
     * Get target description
     */
    public function getTargetDescription(): string
    {
        return match ($this->target_type) {
            'user' => $this->user ? $this->user->name : 'مستخدم غير محدد',
            'class' => $this->class ? $this->class->name : 'فصل غير محدد',
            'all_students' => 'جميع الطلاب',
            'all_teachers' => 'جميع المعلمين',
            'all_users' => 'جميع المستخدمين',
            default => 'غير محدد',
        };
    }
}
