<?php

namespace App\Bots;

use Plasticode\Semantics\Attitude;
use Plasticode\Semantics\Gender;

class SberRequest extends AbstractBotRequest
{
    const STATE_ROOT = 'intent';
    const USER_STATE = 'u';
    const APPLICATION_STATE = 'a';
    const SERVER_ACTION = 'SERVER_ACTION';

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
        $messageName = $data['messageName'] ?? null;

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

        $isServerAction = ($messageName === self::SERVER_ACTION);

        $this->isButtonPressed = $isServerAction;

        if ($isServerAction) {
            $this->originalCommand ??= $this->payload['server_action']['action_id'] ?? null;
        }

        if (strlen($this->originalCommand) > 0) {
            $this->originalCommand = mb_strtolower($this->originalCommand);
        }

        $this->tokens = $this->parseTokens($this->originalCommand);
        $this->command = $this->rebuildFrom($this->tokens);

        // in case of server action token list is empty
        if (empty($this->originalTokens)) {
            $this->originalTokens = $this->tokens;
        }

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

        if ($appeal === 'no_official') {
            $this->attitude = Attitude::UNOFFICIAL;
        }
    }

    protected function getTrashTokens(): array
    {
        return array_merge(
            parent::getTrashTokens(),
            ['салют', 'salute', 'сбер', 'sber', 'джой', 'joy']
        );
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

            $tokens[] = mb_strtolower($tokenData['text']);
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
