<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create payment for student
     */
    public function createPayment(Request $request, int $studentId): JsonResponse
    {
        // Check authorization - only admin can create payments
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admin can create payments');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,online',
                'status' => 'nullable|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|unique:payments,transaction_id',
            'due_date' => 'required|date',
            'paid_date' => 'nullable|date|after_or_equal:due_date',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $student = User::findOrFail($studentId);

        $payment = Payment::create([
            'student_id' => $studentId,
            'subscription_id' => $request->subscription_id,
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'SAR',
            'payment_method' => $request->payment_method,
            'status' => $request->status ?? 'pending',
            'transaction_id' => $request->transaction_id,
            'due_date' => $request->due_date,
            'paid_date' => $request->paid_date,
            'notes' => $request->notes,
        ]);

        // If payment is completed and has subscription, extend subscription
        if ($payment->isSuccessful() && $payment->subscription_id) {
            $subscription = Subscription::find($payment->subscription_id);
            if ($subscription) {
                // Extend subscription by 30 days
                $subscription->update([
                    'end_date' => $subscription->end_date->addDays(30),
                    'status' => 'active',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => [
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                    'due_date' => $payment->due_date->format('Y-m-d H:i:s'),
                    'paid_date' => $payment->paid_date ? $payment->paid_date->format('Y-m-d H:i:s') : null,
                    'notes' => $payment->notes,
                    'is_successful' => $payment->isSuccessful(),
                    'is_pending' => $payment->isPending(),
                    'is_failed' => $payment->isFailed(),
                ],
            ],
        ]);
    }

    /**
     * Get student payments
     */
    public function getStudentPayments(int $studentId): JsonResponse
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isTeacher() && $user->id !== $studentId) {
            abort(403, 'Unauthorized to access payments');
        }

        $student = User::findOrFail($studentId);
        $payments = Payment::where('student_id', $studentId)
            ->with('subscription')
            ->orderBy('due_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
                'payments' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status,
                        'transaction_id' => $payment->transaction_id,
                        'due_date' => $payment->due_date->format('Y-m-d H:i:s'),
                        'paid_date' => $payment->paid_date ? $payment->paid_date->format('Y-m-d H:i:s') : null,
                        'notes' => $payment->notes,
                        'subscription' => $payment->subscription ? [
                            'id' => $payment->subscription->id,
                            'end_date' => $payment->subscription->end_date->format('Y-m-d H:i:s'),
                        ] : null,
                    ];
                }),
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                    'successful_payments' => $payments->where('status', 'completed')->count(),
            ],
        ]);
    }
}