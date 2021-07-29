<?php

namespace App\Bots;

use Plasticode\Semantics\Attitude;
use Plasticode\Semantics\Gender;

class SberRequest extends AbstractBotRequest
{
    public const STATE_ROOT = 'intent';
    public const USER_STATE = 'u';
    public const APPLICATION_STATE = 'a';

    public ?int $messageId;
    public ?string $sessionId;

    public array $uuid;

    public ?string $userChannel;

    public array $payload;
    public array $device;

    public function __construct(array $data)
    {
        parent::__construct();

        $this->messageId = $data['messageId'] ?? 0;
        $this->sessionId = $data['sessionId'] ?? null;

        $this->uuid = $data['uuid'] ?? [];

        $this->applicationId = $this->uuid['userId'] ?? null;
        $this->userId = $this->uuid['sub'] ?? null;
        $this->userChannel = $this->uuid['userChannel'] ?? null;

        $this->payload = $data['payload'] ?? [];
        $this->device = $this->payload['device'] ?? [];

        $this->isNewSession = $this->payload['new_session'] ?? true;

        $message = $this->payload['message'] ?? [];

        $this->originalCommand = $message['original_text'] ?? null;
        $this->originalTokens = $this->getOriginalTokens($message);

        $this->tokens = $this->parseTokens($this->originalCommand);
        $this->command = $this->rebuildFrom($this->tokens);

        $state = $this->payload[self::STATE_ROOT] ?? null;

        $decodedState = $this->decodeState($state) ?? [];

        $this->userState = $decodedState[self::USER_STATE] ?? null;
        $this->applicationState = $decodedState[self::APPLICATION_STATE] ?? null;

        $character = $this->payload['character'] ?? [];
        $gender = $character['gender'] ?? null;
        $appeal = $character['appeal'] ?? null;

        if ($gender === 'female') {
            $this->gender = Gender::FEM;
        }

        if ($appeal === 'unofficial') {
            $this->attitude = Attitude::UNOFFICIAL;
        }
    }

    /**
     * @return string[]
     */
    private function getOriginalTokens(array $message): array
    {
        $tokens = [];

        $tokenRoot = $message['tokenized_elements_list'] ?? [];

        foreach ($tokenRoot as $tokenData) {
            $tokenType = $tokenData['token_type'] ?? null;

            if ($tokenType === 'SENTENCE_ENDPOINT_TOKEN') {
                continue;
            }

            $tokens[] = $tokenData['text'];
        }

        return $tokens;
    }

    private function decodeState(?string $rawState): ?array
    {
        if (strlen($rawState) === 0) {
            return null;
        }

        return json_decode($rawState, true);
    }
}
