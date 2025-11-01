<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification via multiple channels
     */
    public function sendNotification(Notification $notification): bool
    {
        try {
            $user = $notification->user;
            
            switch ($notification->channel) {
                case 'email':
                    $this->sendEmailNotification($user, $notification);
                    break;
                case 'sms':
                    $this->sendSmsNotification($user, $notification);
                    break;
                case 'push':
                    $this->sendPushNotification($user, $notification);
                    break;
                case 'in_app':
                    // In-app notifications are just stored, no sending needed
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported channel: {$notification->channel}");
            }

            $notification->markAsSent();
            return true;
        } catch (\Exception $e) {
            Log::error('Notification sending failed', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'channel' => $notification->channel,
                'error' => $e->getMessage(),
            ]);

            $notification->update(['status' => 'failed']);
            return false;
        }
    }

    /**
     * Send notification via multiple channels (legacy method)
     */
    public function sendNotificationLegacy(
        User $user,
        string $type,
        string $title,
        string $message,
        string $channel = 'push',
        array $data = []
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'channel' => $channel,
            'data' => $data,
            'status' => 'pending',
        ]);

        $this->sendNotification($notification);
        return $notification;
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(User $user, Notification $notification): void
    {
        if (!$user->email) {
            throw new \Exception('User email not found');
        }

        $subject = $notification->email_subject ?: $notification->title;
        $template = $notification->email_template ?: $this->getEmailTemplate($notification->type);
        
        // If custom template is provided, use it directly
        if ($notification->email_template) {
            $message = $this->formatMessage($notification->email_template, $user, $notification);
            
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)->subject($subject);
            });
        } else {
            // Use predefined template
            Mail::send($template, [
                'user' => $user,
                'notification' => $notification,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
            ], function ($mail) use ($user, $subject) {
                $mail->to($user->email)->subject($subject);
            });
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsAppNotification(User $user, Notification $notification): void
    {
        if (!$user->phone) {
            throw new \Exception('User phone not found');
        }

        $template = $this->getWhatsAppTemplate($notification->type);
        $message = $this->formatMessage($template, $user, $notification);

        // TODO: Integrate with WhatsApp Business API
        // For now, just log the message
        Log::info('WhatsApp notification', [
            'phone' => $user->phone,
            'message' => $message,
        ]);
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(User $user, Notification $notification): void
    {
        // Get active devices (devices seen within last 30 days)
        $devices = $user->devices()->get()->filter(function ($device) {
            return $device->isActive() && $device->fcm_token;
        });
        
        if ($devices->isEmpty()) {
            throw new \Exception('No active devices with FCM tokens found');
        }

        $template = $this->getPushTemplate($notification->type);
        $payload = $this->formatPushPayload($template, $user, $notification);

        foreach ($devices as $device) {
            $this->sendFCMNotification($device->fcm_token, $payload);
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(User $user, Notification $notification): void
    {
        if (!$user->phone) {
            throw new \Exception('User phone not found');
        }

        $template = $notification->sms_template ?: $this->getSmsTemplate($notification->type);
        $message = $this->formatMessage($template, $user, $notification);

        // TODO: Integrate with SMS provider (Twilio, etc.)
        // For now, just log the message
        Log::info('SMS notification', [
            'phone' => $user->phone,
            'message' => $message,
            'notification_id' => $notification->id,
        ]);
        
        // Simulate successful sending for testing
        // In production, replace with actual SMS API call
    }

    /**
     * Send FCM notification using HTTP v1 API
     */
    private function sendFCMNotification(string $fcmToken, array $payload): void
    {
        $fcmService = app(\App\Services\FcmService::class);
        $fcmService->sendNotification($fcmToken, $payload);
    }

    /**
     * Get email template name
     */
    private function getEmailTemplate(string $type): string
    {
        return match ($type) {
            'payment_reminder' => 'emails.payment-reminder',
            'subscription_expiry' => 'emails.subscription-expiry',
            'payment_success' => 'emails.payment-success',
            'payment_failed' => 'emails.payment-failed',
            'general' => 'emails.general',
            'system' => 'emails.system',
            default => 'emails.general',
        };
    }

    /**
     * Get WhatsApp template
     */
    private function getWhatsAppTemplate(string $type): string
    {
        return match ($type) {
            'payment_reminder' => 'مرحباً {{user_name}}، تذكير بدفع رسوم الاشتراك. المبلغ: {{amount}} {{currency}}',
            'subscription_expiry' => 'مرحباً {{user_name}}، اشتراكك سينتهي في {{expiry_date}}. يرجى تجديد الاشتراك.',
            'payment_success' => 'مرحباً {{user_name}}، تم استلام دفعتك بنجاح. شكراً لك!',
            'payment_failed' => 'مرحباً {{user_name}}، فشل في معالجة دفعتك. يرجى المحاولة مرة أخرى.',
            'general' => '{{message}}',
            'system' => 'إشعار من النظام: {{message}}',
            default => '{{message}}',
        };
    }

    /**
     * Get push template
     */
    private function getPushTemplate(string $type): array
    {
        // Return default template - actual title and body come from Notification model
        return [
            'title' => 'إشعار جديد',
            'body' => 'لديك إشعار جديد',
        ];
    }

    /**
     * Get SMS template
     */
    private function getSmsTemplate(string $type): string
    {
        return match ($type) {
            'payment_reminder' => 'مرحباً {{user_name}}، تذكير بدفع رسوم الاشتراك. المبلغ: {{amount}} {{currency}}',
            'subscription_expiry' => 'مرحباً {{user_name}}، اشتراكك سينتهي في {{expiry_date}}',
            'payment_success' => 'مرحباً {{user_name}}، تم استلام دفعتك بنجاح',
            'payment_failed' => 'مرحباً {{user_name}}، فشل في معالجة دفعتك',
            'general' => '{{message}}',
            'system' => 'إشعار من النظام: {{message}}',
            default => '{{message}}',
        };
    }

    /**
     * Format message with placeholders
     */
    private function formatMessage(string $template, User $user, Notification $notification): string
    {
        $placeholders = [
            '{{user_name}}' => $user->name,
            '{{message}}' => $notification->message,
            '{{amount}}' => $notification->data['amount'] ?? '',
            '{{currency}}' => $notification->data['currency'] ?? 'SAR',
            '{{expiry_date}}' => $notification->data['expiry_date'] ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    /**
     * Format push notification payload
     */
    private function formatPushPayload(array $template, User $user, Notification $notification): array
    {
        // Use notification's title and message instead of template
        return [
            'title' => $notification->title,
            'body' => $notification->message,
            'data' => [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'user_id' => $user->id,
                'custom_data' => $notification->data ?? [],
            ],
        ];
    }
}