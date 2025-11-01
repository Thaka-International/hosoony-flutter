<?php

namespace App\Services;

use App\Models\VerificationCode;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppVerificationService
{
    protected $twilioClient;
    protected $whatsappFrom;

    public function __construct()
    {
        // Only initialize Twilio if credentials are available
        if (config('services.twilio.sid') && config('services.twilio.token')) {
            $this->twilioClient = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
            $this->whatsappFrom = config('services.twilio.whatsapp_from');
        }
    }

    /**
     * Send verification code via WhatsApp.
     */
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        // If Twilio is not configured, just log and return true for testing
        if (!$this->twilioClient) {
            Log::info('WhatsApp verification code (Twilio not configured)', [
                'phone' => $phoneNumber,
                'code' => $code,
            ]);
            return true;
        }

        try {
            // Format phone number for WhatsApp (add country code if missing)
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            $message = "مرحباً بك في حسوني 🕌\n\n";
            $message .= "رمز التحقق الخاص بك هو: *{$code}*\n\n";
            $message .= "هذا الرمز صالح لمدة 5 دقائق فقط.\n";
            $message .= "لا تشارك هذا الرمز مع أي شخص آخر.\n\n";
            $message .= "شكراً لاستخدامك حسوني 📚";

            $messageResponse = $this->twilioClient->messages->create(
                "whatsapp:{$formattedPhone}",
                [
                    'from' => "whatsapp:{$this->whatsappFrom}",
                    'body' => $message,
                ]
            );

            Log::info('WhatsApp verification code sent', [
                'phone' => $phoneNumber,
                'message_sid' => $messageResponse->sid,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp verification code', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate and send verification code for phone number.
     */
    public function generateAndSendCode(string $phoneNumber, string $type = 'login'): ?VerificationCode
    {
        $verificationCode = VerificationCode::createForPhone($phoneNumber, $type);
        
        if ($this->sendVerificationCode($phoneNumber, $verificationCode->code)) {
            return $verificationCode;
        }

        // If sending failed, delete the code
        $verificationCode->delete();
        return null;
    }

    /**
     * Format phone number for WhatsApp.
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add Saudi country code if missing
        if (strlen($phoneNumber) === 9 && str_starts_with($phoneNumber, '5')) {
            $phoneNumber = '966' . $phoneNumber;
        } elseif (strlen($phoneNumber) === 10 && str_starts_with($phoneNumber, '05')) {
            $phoneNumber = '966' . substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    /**
     * Verify code and return user if valid.
     */
    public function verifyCodeAndLogin(string $phoneNumber, string $code): ?\App\Models\User
    {
        $verificationCode = VerificationCode::verifyCode($phoneNumber, $code);
        
        if (!$verificationCode) {
            return null;
        }

        // Find user by phone number
        $user = \App\Models\User::where('phone', $phoneNumber)->first();
        
        if (!$user) {
            // If user doesn't exist, create a new student account
            $user = \App\Models\User::create([
                'name' => 'طالب جديد',
                'phone' => $phoneNumber,
                'role' => 'student',
                'status' => 'active',
                'password' => bcrypt('temp_password'), // Will be changed on first login
            ]);
        }

        return $user;
    }

    /**
     * Send welcome message after successful login.
     */
    public function sendWelcomeMessage(string $phoneNumber, string $userName): bool
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            $message = "مرحباً {$userName} 👋\n\n";
            $message .= "تم تسجيل دخولك بنجاح إلى حسوني!\n\n";
            $message .= "يمكنك الآن الوصول إلى:\n";
            $message .= "• مهامك اليومية 📝\n";
            $message .= "• جدولك الأسبوعي 📅\n";
            $message .= "• نقاطك وشاراتك 🏆\n";
            $message .= "• توصيات المعلم 📚\n\n";
            $message .= "نتمنى لك تجربة ممتعة في رحلتك التعليمية! 🌟";

            $this->twilioClient->messages->create(
                "whatsapp:{$formattedPhone}",
                [
                    'from' => "whatsapp:{$this->whatsappFrom}",
                    'body' => $message,
                ]
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send welcome message', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
