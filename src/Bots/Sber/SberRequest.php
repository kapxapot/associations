<?php

namespace App\Bots\Sber;

use App\Bots\AbstractRequest;

class SberRequest extends AbstractRequest
{
    public ?int $messageId;
    public ?string $sessionId;

    public array $uuid;

    public ?string $userChannel;

    public array $payload;
    public array $device;

    public array $appInfo;

    public ?string $projectId;

    public function __construct(array $data)
    {
        $this->messageId = $data['messageId'] ?? 0;
        $this->sessionId = $data['sessionId'] ?? null;

        $this->uuid = $data['uuid'] ?? [];

        $this->applicationId = $this->uuid['userId'] ?? null;
        $this->userId = $this->uuid['sub'] ?? null;
        $this->userChannel = $this->uuid['userChannel'] ?? null;

        $this->payload = $data['payload'] ?? [];
        $this->device = $this->payload['device'] ?? [];

        $this->appInfo = $this->payload['app_info'] ?? [];
        $this->projectId = $this->appInfo['projectId'] ?? null;
    }
}
