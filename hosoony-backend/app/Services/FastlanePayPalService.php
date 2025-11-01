<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FastlanePayPalService
{
    private string $apiKey;
    private string $baseUrl;
    private bool $isSandbox;

    public function __construct()
    {
        $this->apiKey = config('services.fastlane_paypal.api_key');
        $this->isSandbox = config('services.fastlane_paypal.sandbox', true);
        $this->baseUrl = $this->isSandbox 
            ? 'https://sandbox-api.fastlane.com' 
            : 'https://api.fastlane.com';
    }

    /**
     * Create Fastlane PayPal payment
     */
    public function createPayment(Payment $payment): array
    {
        try {
            $paymentData = [
                'amount' => [
                    'value' => number_format($payment->amount, 2, '.', ''),
                    'currency' => $payment->currency,
                ],
                'description' => 'دفع رسوم الاشتراك - ' . $payment->student->name,
                'reference_id' => 'payment_' . $payment->id,
                'payment_method' => [
                    'type' => 'paypal',
                    'paypal' => [
                        'experience_context' => [
                            'brand_name' => 'حصوني LMS',
                            'locale' => 'ar-SA',
                            'landing_page' => 'NO_PREFERENCE',
                            'user_action' => 'PAY_NOW',
                            'return_url' => route('payment.fastlane.success'),
                            'cancel_url' => route('payment.fastlane.cancel'),
                        ]
                    ]
                ],
                'metadata' => [
                    'payment_id' => $payment->id,
                    'student_id' => $payment->student_id,
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'X-Request-ID' => uniqid(),
            ])->post($this->baseUrl . '/v1/payments', $paymentData);

            if ($response->successful()) {
                $fastlanePayment = $response->json();
                
                // Update payment with Fastlane payment ID
                $payment->update([
                    'transaction_id' => $fastlanePayment['id'],
                    'status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'payment_id' => $fastlanePayment['id'],
                    'approval_url' => $this->getApprovalUrl($fastlanePayment),
                ];
            }

            throw new Exception('Failed to create Fastlane PayPal payment: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Fastlane PayPal payment creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Capture Fastlane PayPal payment
     */
    public function capturePayment(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/payments/' . $paymentId . '/capture');

            if ($response->successful()) {
                $capture = $response->json();
                
                // Find payment by transaction ID
                $payment = Payment::where('transaction_id', $paymentId)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'paid_date' => now(),
                        'notes' => 'Payment captured via Fastlane PayPal. Capture ID: ' . $capture['id'],
                    ]);

                    // Update subscription status
                    if ($payment->subscription) {
                        $payment->subscription->update(['status' => 'active']);
                    }
                }

                return [
                    'success' => true,
                    'capture' => $capture,
                ];
            }

            throw new Exception('Failed to capture Fastlane PayPal payment: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Fastlane PayPal payment capture failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/v1/payments/' . $paymentId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'payment' => $response->json(),
                ];
            }

            throw new Exception('Failed to get Fastlane PayPal payment status: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Fastlane PayPal payment status check failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            $refundData = [];
            if ($amount) {
                $refundData['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/payments/' . $paymentId . '/refund', $refundData);

            if ($response->successful()) {
                $refund = $response->json();
                
                // Find payment by transaction ID
                $payment = Payment::where('transaction_id', $paymentId)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'refunded',
                        'notes' => 'Payment refunded via Fastlane PayPal. Refund ID: ' . $refund['id'],
                    ]);
                }

                return [
                    'success' => true,
                    'refund' => $refund,
                ];
            }

            throw new Exception('Failed to refund Fastlane PayPal payment: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Fastlane PayPal payment refund failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get approval URL from payment
     */
    private function getApprovalUrl(array $payment): string
    {
        foreach ($payment['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        throw new Exception('Approval URL not found in Fastlane PayPal payment');
    }

    /**
     * Handle Fastlane webhook events
     */
    public function handleWebhook(array $event): void
    {
        try {
            switch ($event['event_type']) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCompleted($event);
                    break;
                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePaymentDenied($event);
                    break;
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentRefunded($event);
                    break;
                default:
                    Log::info('Unhandled Fastlane PayPal webhook event', [
                        'event_type' => $event['event_type'],
                    ]);
            }
        } catch (Exception $e) {
            Log::error('Fastlane PayPal webhook handling failed', [
                'event_type' => $event['event_type'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle payment completed event
     */
    private function handlePaymentCompleted(array $event): void
    {
        $capture = $event['resource'];
        $metadata = $capture['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'completed',
                    'paid_date' => now(),
                    'notes' => 'Payment completed via Fastlane PayPal webhook',
                ]);
            }
        }
    }

    /**
     * Handle payment denied event
     */
    private function handlePaymentDenied(array $event): void
    {
        $capture = $event['resource'];
        $metadata = $capture['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'failed',
                    'notes' => 'Payment denied via Fastlane PayPal webhook',
                ]);
            }
        }
    }

    /**
     * Handle payment refunded event
     */
    private function handlePaymentRefunded(array $event): void
    {
        $capture = $event['resource'];
        $metadata = $capture['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment) {
                $payment->update([
                    'status' => 'refunded',
                    'notes' => 'Payment refunded via Fastlane PayPal webhook',
                ]);
            }
        }
    }
}


