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
        $this->originalTokens = $this->cleanAliceTokens($request['nlu']['tokens'] ?? []);
        $this->originalUtterance = $request['original_utterance'];

        $command = $this->originalUtterance ?? $request['payload'] ?? null;

        if (strlen($command) > 0) {
            $command = mb_strtolower($command);
        }

        $this->dirtyTokens = $this->parseTokens($command);
        $this->tokens = $this->cleanTokens($this->dirtyTokens);
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

    /**
     * Cleans Alice tokens such as "*".
     *
     * @param string[] $tokens
     * @return string[]
     */
    private function cleanAliceTokens(array $tokens): array
    {
        if ($tokens === null) {
            return [];
        }

        $badTokens = ['*'];

        return array_filter(
            $tokens,
            fn ($token) => !in_array($token, $badTokens)
        );
    }
}
