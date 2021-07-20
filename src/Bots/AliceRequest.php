<?php

namespace App\Bots;

class AliceRequest extends AbstractBotRequest
{
    public function __construct(array $data)
    {
        parent::__construct();

        $this->originalCommand = $data['request']['command'] ?? null;
        $this->originalTokens = $data['request']['nlu']['tokens'] ?? [];

        $originalUtterance = $data['request']['original_utterance'] ?? null;

        $this->tokens = $this->parseTokens($originalUtterance);
        $this->command = $this->rebuildFrom($this->tokens);

        $this->isNewSession = $data['session']['new'] ?? true;

        $this->userId = $data['session']['user']['user_id'] ?? null;
        $this->applicationId = $data['session']['application']['application_id'] ?? null;

        $this->userState = $data['state']['user'] ?? null;
        $this->applicationState = $data['state']['application'] ?? null;
    }

    protected function getTrashTokens(): array
    {
        return [
            'говорю', 'алиса', 'блядь', 'алис', 'сама', 'этот', 'это', 'так', 'ты', 'ой', 'да', 'ну', 'я', 'э', 'а', '-', '='
        ];
    }
}
