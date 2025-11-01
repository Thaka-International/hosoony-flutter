<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Send test notification (admin only)
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        // Check authorization - only admin can send test notifications
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admin can send test notifications');
        }

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'type' => 'required|in:info,success,warning,error,reminder',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'channel' => 'required|in:in_app,email,sms,push',
                'data' => 'nullable|array',
            ]);

        $targetUser = User::findOrFail($request->user_id);

        $notification = $this->notificationService->sendNotificationLegacy(
            $targetUser,
            $request->type,
            $request->title,
            $request->message,
            $request->channel,
            $request->data ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent successfully',
            'data' => [
                'notification' => [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'channel' => $notification->channel,
                    'status' => $notification->status,
                    'sent_at' => $notification->sent_at?->format('Y-m-d H:i:s'),
                ],
                'target_user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                ],
            ],
        ]);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'channel' => $notification->channel,
                        'status' => $notification->status,
                        'sent_at' => $notification->sent_at?->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                        'is_read' => $notification->isRead(),
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): JsonResponse
    {
        $user = auth()->user();
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'notification' => [
                    'id' => $notification->id,
                    'read_at' => $notification->read_at->format('Y-m-d H:i:s'),
                    'is_read' => $notification->isRead(),
                ],
            ],
        ]);
    }
}