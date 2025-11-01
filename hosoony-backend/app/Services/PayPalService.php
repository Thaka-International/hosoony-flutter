<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private bool $isSandbox;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->isSandbox = config('services.paypal.sandbox', true);
        $this->baseUrl = $this->isSandbox 
            ? 'https://api.sandbox.paypal.com' 
            : 'https://api.paypal.com';
    }

    /**
     * Create PayPal order
     */
    public function createOrder(Payment $payment): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'payment_' . $payment->id,
                        'amount' => [
                            'currency_code' => $payment->currency,
                            'value' => number_format($payment->amount, 2, '.', '')
                        ],
                        'description' => 'دفع رسوم الاشتراك - ' . $payment->student->name,
                        'custom_id' => $payment->id,
                    ]
                ],
                'application_context' => [
                    'brand_name' => 'حصوني LMS',
                    'landing_page' => 'NO_PREFERENCE',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('payment.paypal.success'),
                    'cancel_url' => route('payment.paypal.cancel'),
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => uniqid(),
            ])->post($this->baseUrl . '/v2/checkout/orders', $orderData);

            if ($response->successful()) {
                $order = $response->json();
                
                // Update payment with PayPal order ID
                $payment->update([
                    'transaction_id' => $order['id'],
                    'status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'order_id' => $order['id'],
                    'approval_url' => $this->getApprovalUrl($order),
                ];
            }

            throw new Exception('Failed to create PayPal order: ' . $response->body());

        } catch (Exception $e) {
            Log::error('PayPal order creation failed', [
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
     * Capture PayPal order
     */
    public function captureOrder(string $orderId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');

            if ($response->successful()) {
                $capture = $response->json();
                
                // Find payment by order ID
                $payment = Payment::where('transaction_id', $orderId)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'paid_date' => now(),
                        'notes' => 'Payment captured via PayPal. Capture ID: ' . $capture['id'],
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

            throw new Exception('Failed to capture PayPal order: ' . $response->body());

        } catch (Exception $e) {
            Log::error('PayPal order capture failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal access token
     */
    private function getAccessToken(): string
    {
        $response = Http::asForm()->withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new Exception('Failed to get PayPal access token: ' . $response->body());
    }

    /**
     * Get approval URL from order
     */
    private function getApprovalUrl(array $order): string
    {
        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        throw new Exception('Approval URL not found in PayPal order');
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $payload, array $headers): bool
    {
        try {
            $webhookId = config('services.paypal.webhook_id');
            $certId = $headers['paypal-cert-id'] ?? '';
            $authAlgo = $headers['paypal-auth-algo'] ?? '';
            $transmissionId = $headers['paypal-transmission-id'] ?? '';
            $transmissionSig = $headers['paypal-transmission-sig'] ?? '';
            $transmissionTime = $headers['paypal-transmission-time'] ?? '';

            $verificationData = [
                'auth_algo' => $authAlgo,
                'cert_id' => $certId,
                'transmission_id' => $transmissionId,
                'transmission_sig' => $transmissionSig,
                'transmission_time' => $transmissionTime,
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true),
            ];

            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/notifications/verify-webhook-signature', $verificationData);

            return $response->successful() && $response->json()['verification_status'] === 'SUCCESS';

        } catch (Exception $e) {
            Log::error('PayPal webhook verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle PayPal webhook events
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
                    Log::info('Unhandled PayPal webhook event', [
                        'event_type' => $event['event_type'],
                    ]);
            }
        } catch (Exception $e) {
            Log::error('PayPal webhook handling failed', [
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
        $customId = $capture['custom_id'] ?? null;

        if ($customId) {
            $payment = Payment::find($customId);
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'completed',
                    'paid_date' => now(),
                    'notes' => 'Payment completed via PayPal webhook',
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
        $customId = $capture['custom_id'] ?? null;

        if ($customId) {
            $payment = Payment::find($customId);
            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'failed',
                    'notes' => 'Payment denied via PayPal webhook',
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
        $customId = $capture['custom_id'] ?? null;

        if ($customId) {
            $payment = Payment::find($customId);
            if ($payment) {
                $payment->update([
                    'status' => 'refunded',
                    'notes' => 'Payment refunded via PayPal webhook',
                ]);
            }
        }
    }
}


