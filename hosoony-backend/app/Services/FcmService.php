<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    private ?GoogleClient $client = null;
    private string $projectId;

    public function __construct()
    {
        $this->projectId = env('FCM_PROJECT_ID', 'hosoony-abbba');
    }

    /**
     * Get OAuth2 access token for FCM HTTP v1 API
     */
    private function getAccessToken(): string
    {
        // Cache the token for 50 minutes (tokens are valid for 1 hour)
        return Cache::remember('fcm_access_token', 50 * 60, function () {
            try {
                $client = $this->getGoogleClient();
                $client->fetchAccessTokenWithAssertion();
                $token = $client->getAccessToken();
                
                if (isset($token['access_token'])) {
                    return $token['access_token'];
                }
                
                throw new \Exception('Failed to get access token');
            } catch (\Exception $e) {
                Log::error('FCM access token retrieval failed', [
                    'error' => $e->getMessage(),
                ]);
                throw new \Exception('Failed to obtain FCM access token: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get Google Client instance with service account credentials
     */
    private function getGoogleClient(): GoogleClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $this->client = new GoogleClient();
        
        // Option 1: Service Account JSON file path (Recommended)
        $serviceAccountPath = env('FCM_SERVICE_ACCOUNT_PATH');
        if ($serviceAccountPath && file_exists($serviceAccountPath)) {
            $this->client->setAuthConfig($serviceAccountPath);
            $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            return $this->client;
        }
        
        // Option 2: Service Account JSON content from env variable
        $serviceAccountJson = env('FCM_SERVICE_ACCOUNT_JSON');
        if ($serviceAccountJson) {
            $decoded = json_decode($serviceAccountJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->client->setAuthConfig($decoded);
                $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                return $this->client;
            }
        }
        
        // Option 3: Individual service account credentials (for migration)
        $clientEmail = env('FCM_CLIENT_EMAIL');
        $privateKey = env('FCM_PRIVATE_KEY');
        
        if ($clientEmail && $privateKey) {
            $config = [
                'type' => 'service_account',
                'project_id' => $this->projectId,
                'private_key_id' => env('FCM_PRIVATE_KEY_ID'),
                'private_key' => str_replace('\\n', "\n", $privateKey),
                'client_email' => $clientEmail,
                'client_id' => env('FCM_CLIENT_ID'),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => env('FCM_CLIENT_X509_CERT_URL'),
            ];
            
            // Only set if we have minimum required fields
            if (!empty($config['client_email']) && !empty($config['private_key'])) {
                $this->client->setAuthConfig($config);
                $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                return $this->client;
            }
        }
        
        throw new \Exception('FCM service account not configured. Please set FCM_SERVICE_ACCOUNT_PATH, FCM_SERVICE_ACCOUNT_JSON, or FCM service account credentials in .env');
    }

    /**
     * Send FCM notification using HTTP v1 API
     */
    public function sendNotification(string $fcmToken, array $payload): void
    {
        try {
            $accessToken = $this->getAccessToken();
            
            // HTTP v1 API endpoint
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
            
            // HTTP v1 payload structure
            $data = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $payload['title'],
                        'body' => $payload['body'],
                    ],
                ],
            ];

            // Add data payload if present
            if (!empty($payload['data'])) {
                $data['message']['data'] = $payload['data'];
            }

            // Add platform-specific configurations
            if (isset($payload['android'])) {
                $data['message']['android'] = $payload['android'];
            }
            
            if (isset($payload['apns'])) {
                $data['message']['apns'] = $payload['apns'];
            }
            
            if (isset($payload['webpush'])) {
                $data['message']['webpush'] = $payload['webpush'];
            }

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception("FCM curl error: {$error}");
            }
            
            curl_close($ch);

            if ($httpCode !== 200) {
                $errorDetails = json_decode($response, true);
                throw new \Exception("FCM HTTP v1 request failed with HTTP code: {$httpCode}. Response: " . json_encode($errorDetails));
            }

            $result = json_decode($response, true);
            
            // HTTP v1 returns 'name' on success instead of checking 'failure' field
            if (!isset($result['name'])) {
                throw new \Exception('FCM notification failed: ' . json_encode($result));
            }

            Log::info('FCM notification sent successfully', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'response_name' => $result['name'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('FCM HTTP v1 notification failed', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

