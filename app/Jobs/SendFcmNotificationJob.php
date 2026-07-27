<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public string $fcmToken;
    public string $title;
    public string $message;
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fcmToken, string $title, string $message, array $data = [])
    {
        $this->fcmToken = $fcmToken;
        $this->title    = $title;
        $this->message  = $message;
        $this->data     = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcmService): void
    {
        if (empty($this->fcmToken)) {
            return;
        }

        try {
            $fcmService->sendToToken($this->fcmToken, $this->title, $this->message, $this->data);
        } catch (\Throwable $e) {
            Log::error("SendFcmNotificationJob failed for token [{$this->fcmToken}]: " . $e->getMessage());
        }
    }
}
