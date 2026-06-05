<?php

namespace CustomFeature\ClassRankerApi\Services;

use CustomFeature\ClassRankerApi\Jobs\SendBulkNotificationJob;
use Google\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected string $projectId;

    // FCM ki max chunk size — 500 safe limit
    const CHUNK_SIZE = 500;

    public function __construct()
    {
        $this->projectId = core()->getConfigData('class_ranker.settings.firebase.project_id');
    }

    /**
     * Single customer ko notify karo
     */
    public function sendToCustomer(
        int $customerId,
        string $title,
        string $body,
        array $data = []
    ): void {
        $token = DB::table('customer_devices')
            ->where('customer_id', $customerId)
            ->value('fcm_token');

        if (! $token) {
            return;
        }

        $this->sendToToken($token, $title, $body, $data);
    }

    /**
     * Multiple customers — direct (small lists only, <50)
     * Bade lists k liye sendToCustomersQueued use karo
     */
    public function sendToCustomers(
        array $customerIds,
        string $title,
        string $body,
        array $data = []
    ): void {
        $tokens = $this->getTokensForCustomers($customerIds);

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokensBatched($tokens, $title, $body, $data);
    }

    /**
     * Large customer lists k liye — queue pe dispatch karo
     * Admin bulk send ya 1k+ customers k liye yahi use karo
     */
    public function sendToCustomersQueued(
        array $customerIds,
        string $title,
        string $body,
        array $data = []
    ): void {
        // customerIds ko chunks mein tod do, ek ek job dispatch karo
        $chunks = array_chunk($customerIds, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            SendBulkNotificationJob::dispatch($chunk, $title, $body, $data);
        }
    }

    /**
     * Sabhi customers ko notify karo (admin bulk)
     * Direct DB se token chunks pull karta hai — memory safe
     */
    public function sendToAllCustomersQueued(
        string $title,
        string $body,
        array $data = []
    ): void {
        // Seedha customer IDs pull karo jinke paas token hai
        DB::table('customer_devices')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->select('customer_id')
            ->orderBy('customer_id')
            ->chunk(self::CHUNK_SIZE, function ($rows) use ($title, $body, $data) {
                $customerIds = $rows->pluck('customer_id')->toArray();
                SendBulkNotificationJob::dispatch($customerIds, $title, $body, $data);
            });
    }

    /**
     * Single FCM token ko notify karo
     */
    public function sendToToken(
        string $fcmToken,
        string $title,
        string $body,
        array $data = []
    ): void {
        if (empty($fcmToken)) {
            return;
        }

        try {
            $accessToken = $this->getFirebaseAccessToken();

            $response = Http::withToken($accessToken)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                    [
                        'message' => [
                            'token'        => $fcmToken,
                            'notification' => [
                                'title' => $title,
                                'body'  => $body,
                            ],
                            'data' => collect($data)
                                ->map(fn ($value) => (string) $value)
                                ->toArray(),
                        ],
                    ]
                );

            if (! $response->successful()) {
                Log::warning('FCM send failed', [
                    'token'  => substr($fcmToken, 0, 20) . '...',
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM sendToToken exception', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Token list ko chunked batches mein send karo
     * Ek access token lo, sab k liye use karo
     */
    public function sendToTokensBatched(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): void {
        if (empty($tokens)) {
            return;
        }

        try {
            $accessToken = $this->getFirebaseAccessToken();
        } catch (\Exception $e) {
            Log::error('FCM access token fetch failed', ['error' => $e->getMessage()]);
            return;
        }

        $stringData = collect($data)
            ->map(fn ($value) => (string) $value)
            ->toArray();

        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }

            try {
                $response = Http::withToken($accessToken)
                    ->post(
                        "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                        [
                            'message' => [
                                'token'        => $token,
                                'notification' => [
                                    'title' => $title,
                                    'body'  => $body,
                                ],
                                'data' => $stringData,
                            ],
                        ]
                    );

                Log::info('FCM Response', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Exception $e) {
                Log::warning('FCM token send failed', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function getTokensForCustomers(array $customerIds): array
    {
        return DB::table('customer_devices')
            ->whereIn('customer_id', $customerIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getFirebaseAccessToken(): string
    {
        $client = new Client();

        $client->setAuthConfig(
            json_decode(
                core()->getConfigData('class_ranker.settings.firebase.firebase_service'),
                true
            )
        );

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            throw new \RuntimeException('Firebase token error: ' . $token['error_description'] ?? $token['error']);
        }

        return $token['access_token'];
    }
}