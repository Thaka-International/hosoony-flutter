<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PayPalService;
use App\Services\FastlanePayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private PayPalService $paypalService;
    private FastlanePayPalService $fastlaneService;

    public function __construct(PayPalService $paypalService, FastlanePayPalService $fastlaneService)
    {
        $this->paypalService = $paypalService;
        $this->fastlaneService = $fastlaneService;
    }

    /**
     * Show payment form
     */
    public function showPaymentForm(Request $request)
    {
        $user = Auth::user();
        $subscription = Subscription::where('student_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$subscription) {
            return redirect()->route('student.subscription')
                ->with('error', 'لا توجد اشتراك معلق للدفع');
        }

        $payment = Payment::where('subscription_id', $subscription->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return redirect()->route('student.subscription')
                ->with('error', 'لا توجد فاتورة معلقة للدفع');
        }

        return view('pwa.student.payment', compact('payment', 'subscription'));
    }

    /**
     * Process PayPal payment
     */
    public function processPayPalPayment(Request $request)
    {
        $user = Auth::user();
        $payment = Payment::where('student_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد فاتورة معلقة للدفع'
            ], 404);
        }

        $result = $this->paypalService->createOrder($payment);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'approval_url' => $result['approval_url'],
                'order_id' => $result['order_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'فشل في إنشاء طلب الدفع'
        ], 500);
    }

    /**
     * Process Fastlane PayPal payment
     */
    public function processFastlanePayment(Request $request)
    {
        $user = Auth::user();
        $payment = Payment::where('student_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد فاتورة معلقة للدفع'
            ], 404);
        }

        $result = $this->fastlaneService->createPayment($payment);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'approval_url' => $result['approval_url'],
                'payment_id' => $result['payment_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'فشل في إنشاء طلب الدفع'
        ], 500);
    }

    /**
     * Handle PayPal success callback
     */
    public function paypalSuccess(Request $request)
    {
        $orderId = $request->query('token');
        
        if (!$orderId) {
            return redirect()->route('student.subscription')
                ->with('error', 'معرف الطلب غير صحيح');
        }

        $result = $this->paypalService->captureOrder($orderId);

        if ($result['success']) {
            return redirect()->route('student.subscription')
                ->with('success', 'تم الدفع بنجاح!');
        }

        return redirect()->route('student.subscription')
            ->with('error', 'فشل في تأكيد الدفع: ' . ($result['error'] ?? 'خطأ غير معروف'));
    }

    /**
     * Handle PayPal cancel callback
     */
    public function paypalCancel(Request $request)
    {
        return redirect()->route('student.subscription')
            ->with('error', 'تم إلغاء عملية الدفع');
    }

    /**
     * Handle Fastlane success callback
     */
    public function fastlaneSuccess(Request $request)
    {
        $paymentId = $request->query('payment_id');
        
        if (!$paymentId) {
            return redirect()->route('student.subscription')
                ->with('error', 'معرف الدفع غير صحيح');
        }

        $result = $this->fastlaneService->capturePayment($paymentId);

        if ($result['success']) {
            return redirect()->route('student.subscription')
                ->with('success', 'تم الدفع بنجاح!');
        }

        return redirect()->route('student.subscription')
            ->with('error', 'فشل في تأكيد الدفع: ' . ($result['error'] ?? 'خطأ غير معروف'));
    }

    /**
     * Handle Fastlane cancel callback
     */
    public function fastlaneCancel(Request $request)
    {
        return redirect()->route('student.subscription')
            ->with('error', 'تم إلغاء عملية الدفع');
    }

    /**
     * Handle PayPal webhook
     */
    public function paypalWebhook(Request $request)
    {
        $payload = $request->getContent();
        $headers = $request->headers->all();

        // Verify webhook signature
        if (!$this->paypalService->verifyWebhook($payload, $headers)) {
            Log::warning('PayPal webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $this->paypalService->handleWebhook($event);

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Fastlane webhook
     */
    public function fastlaneWebhook(Request $request)
    {
        $payload = $request->getContent();
        $headers = $request->headers->all();

        // Verify webhook signature (implement based on Fastlane documentation)
        $event = json_decode($payload, true);
        $this->fastlaneService->handleWebhook($event);

        return response()->json(['status' => 'success']);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request)
    {
        $user = Auth::user();
        $payment = Payment::where('student_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد فاتورة معلقة'
            ], 404);
        }

        if ($payment->payment_method === 'paypal') {
            $result = $this->paypalService->getPaymentStatus($payment->transaction_id);
        } elseif ($payment->payment_method === 'fastlane_paypal') {
            $result = $this->fastlaneService->getPaymentStatus($payment->transaction_id);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'طريقة دفع غير مدعومة'
            ], 400);
        }

        return response()->json($result);
    }
}