<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get student subscription
     */
    public function getSubscription(int $studentId): JsonResponse
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isTeacher() && $user->id !== $studentId) {
            abort(403, 'Unauthorized to access subscription');
        }

        $student = User::findOrFail($studentId);
        $subscription = Subscription::where('student_id', $studentId)->first();

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                    ],
                    'subscription' => null,
                    'status' => 'no_subscription',
                    'message' => 'لا يوجد اشتراك نشط',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                ],
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'end_date' => $subscription->end_date->format('Y-m-d H:i:s'),
                    'start_date' => $subscription->start_date->format('Y-m-d H:i:s'),
                    'is_active' => $subscription->isActive(),
                    'is_expired' => $subscription->isExpired(),
                    'days_until_expiration' => $subscription->daysUntilExpiration(),
                ],
            ],
        ]);
    }

    /**
     * Update student subscription
     */
    public function updateSubscription(Request $request, int $studentId): JsonResponse
    {
        // Check authorization - only admin can update subscriptions
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admin can update subscriptions');
        }

            $request->validate([
                'end_date' => 'required|date|after:now',
                'status' => 'required|in:active,expired,cancelled,suspended',
                'start_date' => 'nullable|date',
                'fees_plan_id' => 'nullable|exists:fees_plans,id',
            ]);

        $student = User::findOrFail($studentId);
        $subscription = Subscription::where('student_id', $studentId)->first();

        if (!$subscription) {
            $subscription = Subscription::create([
                'student_id' => $studentId,
                'status' => $request->status,
                'end_date' => $request->end_date,
                'fees_plan_id' => $request->fees_plan_id ?? 1, // Default to first fees plan
                'start_date' => $request->start_date ?? now(),
            ]);
        } else {
            $subscription->update([
                'status' => $request->status,
                'end_date' => $request->end_date,
                'start_date' => $request->start_date ?? $subscription->start_date,
                'fees_plan_id' => $request->fees_plan_id ?? $subscription->fees_plan_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'end_date' => $subscription->end_date->format('Y-m-d H:i:s'),
                    'start_date' => $subscription->start_date->format('Y-m-d H:i:s'),
                    'is_active' => $subscription->isActive(),
                    'is_expired' => $subscription->isExpired(),
                    'days_until_expiration' => $subscription->daysUntilExpiration(),
                ],
            ],
        ]);
    }
}