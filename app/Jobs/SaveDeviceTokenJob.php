<?php

namespace App\Jobs;

use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveDeviceTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $fcmToken;

    public function __construct(int $userId, string $fcmToken)
    {
        $this->userId = $userId;
        $this->fcmToken = $fcmToken;
    }

    public function handle()
    {

        DeviceToken::updateOrCreate(
            ['token' => $this->fcmToken],
            ['user_id' => $this->userId]
        );
    }
}
