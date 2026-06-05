<?php

namespace CustomFeature\ClassRankerApi\Jobs;

use CustomFeature\ClassRankerApi\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry attempts — FCM rate limit k case mein
     */
    public int $tries = 3;

    /**
     * Retry delay seconds
     */
    public int $backoff = 30;

    public function __construct(
        protected array $customerIds,
        protected string $title,
        protected string $body,
        protected array $data = []
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        if (empty($this->customerIds)) {
            return;
        }

        Log::info('SendBulkNotificationJob: processing', [
            'customer_count' => count($this->customerIds),
            'title'          => $this->title,
        ]);

        // Tokens fetch karo for this chunk
        $tokens = $notificationService->getTokensForCustomers($this->customerIds);

        if (empty($tokens)) {
            Log::info('SendBulkNotificationJob: no tokens found, skipping');
            return;
        }

        $notificationService->sendToTokensBatched(
            $tokens,
            $this->title,
            $this->body,
            $this->data
        );

        Log::info('SendBulkNotificationJob: done', [
            'token_count' => count($tokens),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendBulkNotificationJob failed', [
            'error'          => $exception->getMessage(),
            'customer_count' => count($this->customerIds),
        ]);
    }
}