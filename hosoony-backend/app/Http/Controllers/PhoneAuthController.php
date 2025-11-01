<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\WhatsAppVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PhoneAuthController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppVerificationService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show phone login form.
     */
    public function showLoginForm()
    {
        return view('auth.phone-login');
    }

    /**
     * Send verification code to phone number.
     */
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:15',
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.min' => 'رقم الجوال قصير جداً',
            'phone.max' => 'رقم الجوال طويل جداً',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المدخلة غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phoneNumber = $this->normalizePhoneNumber($request->phone);

        try {
            $verificationCode = $this->whatsappService->generateAndSendCode($phoneNumber, 'login');

            if ($verificationCode) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إرسال رمز التحقق إلى واتساب الخاص بك',
                    'phone' => $phoneNumber,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إرسال رمز التحقق. يرجى المحاولة مرة أخرى',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في النظام. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }

    /**
     * Verify code and login user.
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ], [
            'phone.required' => 'رقم الجوال مطلوب',
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المدخلة غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phoneNumber = $this->normalizePhoneNumber($request->phone);
        $code = $request->code;

        try {
            $user = $this->whatsappService->verifyCodeAndLogin($phoneNumber, $code);

            if ($user) {
                // Login the user
                Auth::login($user);

                // Send welcome message
                $this->whatsappService->sendWelcomeMessage($phoneNumber, $user->name);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                    ],
                    'redirect' => $this->getRedirectPath($user),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في النظام. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }

    /**
     * Resend verification code.
     */
    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الجوال مطلوب',
            ], 422);
        }

        $phoneNumber = $this->normalizePhoneNumber($request->phone);

        try {
            $verificationCode = $this->whatsappService->generateAndSendCode($phoneNumber, 'login');

            if ($verificationCode) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إعادة إرسال رمز التحقق',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في إعادة إرسال رمز التحقق',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في النظام',
            ], 500);
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('phone.login');
    }

    /**
     * Normalize phone number format.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add Saudi country code if missing
        if (strlen($phone) === 9 && str_starts_with($phone, '5')) {
            $phone = '966' . $phone;
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '05')) {
            $phone = '966' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Get redirect path based on user role.
     */
    protected function getRedirectPath(User $user): string
    {
        switch ($user->role) {
            case 'admin':
            case 'teacher_support':
                return '/admin';
            case 'teacher':
                return '/teacher/dashboard';
            case 'student':
            default:
                return '/student/dashboard';
        }
    }
}