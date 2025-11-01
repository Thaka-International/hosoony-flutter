<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Register or update device with FCM token
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'fcm_token' => 'required|string',
                'platform' => 'required|in:web,android,ios',
            ]);

            $user = Auth::user();
            $platform = $request->platform;
            $fcmToken = $request->fcm_token;

            // Check if device already exists for this user and platform
            $device = Device::where('user_id', $user->id)
                ->where('platform', $platform)
                ->first();

            if ($device) {
                // Update existing device
                $device->update([
                    'fcm_token' => $fcmToken,
                    'last_seen_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device updated successfully',
                    'data' => [
                        'device_id' => $device->id,
                        'platform' => $device->platform,
                        'updated_at' => $device->updated_at->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                // Create new device
                $device = Device::create([
                    'user_id' => $user->id,
                    'fcm_token' => $fcmToken,
                    'platform' => $platform,
                    'last_seen_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device registered successfully',
                    'data' => [
                        'device_id' => $device->id,
                        'platform' => $device->platform,
                        'created_at' => $device->created_at->format('Y-m-d H:i:s'),
                    ],
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error('Device registration failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unregister device (remove FCM token)
     */
    public function unregister(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'platform' => 'required|in:web,android,ios',
            ]);

            $user = Auth::user();
            $platform = $request->platform;

            $device = Device::where('user_id', $user->id)
                ->where('platform', $platform)
                ->first();

            if ($device) {
                // Remove FCM token but keep device record
                $device->update([
                    'fcm_token' => null,
                    'last_seen_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device unregistered successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device unregistration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}



