<?php

namespace App\Bots;

use Plasticode\Semantics\Gender;

class AliceRequest extends AbstractBotRequest
{
    public function __construct(array $data)
    {
        parent::__construct();

        $request = $data['request'] ?? [];

        $this->originalCommand = $request['command'] ?? null;
        $this->originalTokens = $request['nlu']['tokens'] ?? [];

        $command = $request['original_utterance'] ?? $request['payload'] ?? null;

        if (strlen($command) > 0) {
            $command = mb_strtolower($command);
        }

        $this->tokens = $this->parseTokens($command);
        $this->command = $this->rebuildFrom($this->tokens);

        // in case of server action token list *can* be empty
        if (empty($this->originalTokens)) {
            $this->originalTokens = $this->tokens;
        }

        $type = $request['type'] ?? null;

        $this->isButtonPressed = ($type === 'ButtonPressed');

        $session = $data['session'] ?? [];

        $this->isNewSession = $session['new'] ?? true;

        $this->userId = $session['user']['user_id'] ?? null;
        $this->applicationId = $session['application']['application_id'] ?? null;

        $state = $data['state'] ?? [];

        $this->userState = $state['user'] ?? null;
        $this->applicationState = $state['application'] ?? null;

        $this->gender = Gender::FEM;
    }

    protected function getTrashTokens(): array
    {
        return array_merge(
            parent::getTrashTokens(),
            ['алиса', 'алис']
        );
    }
}
