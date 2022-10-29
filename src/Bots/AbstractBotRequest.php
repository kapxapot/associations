<?php

namespace App\Bots;

use App\Semantics\Tokenizer;
use Plasticode\Semantics\Attitude;
use Plasticode\Semantics\Gender;
use Plasticode\Util\Arrays;

abstract class AbstractBotRequest
{
    public const WILDCARD = '*';

    protected Tokenizer $tokenizer;

    protected ?string $applicationId;
    protected ?string $userId;

    protected bool $isNewSession;

    protected ?string $originalUtterance;

    protected ?string $originalCommand;

    /** @var string[] Tokens as they come from a request. */
    protected array $originalTokens;

    protected ?string $command;

    /** @var string[] Tokens built based on the command. */
    protected array $dirtyTokens;

    /** @var string[] Sanitized tokens based on the command. */
    protected array $tokens;

    protected ?array $userState;
    protected ?array $applicationState;

    /** Masculine by default. */
    protected int $gender;

    /** Official by default. */
    protected int $attitude;

    protected bool $isButtonPressed;

    protected function __construct()
    {
        $this->tokenizer = new Tokenizer();

        $this->gender = Gender::MAS;
        $this->attitude = Attitude::OFFICIAL;

        $this->isButtonPressed = false;
    }

    public function hasUser(): bool
    {
        return $this->userId !== null;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function applicationId(): string
    {
        return $this->applicationId;
    }

    public function isNewSession(): bool
    {
        return $this->isNewSession;
    }

    public function originalUtterance(): ?string
    {
        return $this->originalUtterance;
    }

    /**
     * Sanitized command.
     */
    public function command(): ?string
    {
        return $this->command;
    }

    /**
     * @return string[]
     */
    public function originalTokens(): array
    {
        return $this->originalTokens;
    }

    /**
     * Dirty (unsanitized) tokens.
     *
     * @return string[]
     */
    public function dirtyTokens(): array
    {
        return $this->dirtyTokens;
    }

    /**
     * Sanitized tokens.
     *
     * @return string[]
     */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return string[]
     */
    protected function parseTokens(?string $command): array
    {
        return $this->tokenizer->tokenize($command);
    }

    /**
     * Filters trash tokens and trims tokens.
     *
     * @param string[] $tokens
     * @return string[]
     */
    protected function cleanTokens(array $tokens): array
    {
        $trashTokens = $this->getTrashTokens();
        $semiTrashTokens = $this->getSemiTrashTokens();

        // filter trash tokens
        $tokens = $this->filterTokens($tokens, $trashTokens);

        // filter semi-trash tokens in case of more than 1 token
        if (count($tokens) > 1) {
            $tokens = $this->filterTokens($tokens, $semiTrashTokens);
        }

        // trimming
        $trimTokens = $this->getTrimTokens();
        $tokens = $this->trimTokens($tokens, $trimTokens);

        $startingTrashTokens = $this->getStartingTrashTokens();
        $tokens = $this->trimStartingTokens($tokens, $startingTrashTokens);

        $endingTrashTokens = $this->getEndingTrashTokens();
        $tokens = $this->trimEndingTokens($tokens, $endingTrashTokens);

        return $tokens;
    }

    /**
     * Removes bad tokens from tokens.
     *
     * @param string[] $tokens
     * @param string[] $badTokens
     * @return string[]
     */
    private function filterTokens(array $tokens, array $badTokens): array
    {
        $filteredTokens = array_filter(
            $tokens,
            fn (string $t) => !in_array($t, $badTokens)
        );

        return array_values($filteredTokens);
    }

    /**
     * Trims tokens from both the start and the end.
     *
     * @param string[] $tokens
     * @param string[] $badTokens
     * @return string[]
     */
    private function trimTokens(array $tokens, array $badTokens): array
    {
        $tokens = $this->trimStartingTokens($tokens, $badTokens);
        $tokens = $this->trimEndingTokens($tokens, $badTokens);

        return $tokens;
    }

    /**
     * Trims tokens from the start. At least one token is left.
     *
     * @param string[] $tokens
     * @param string[] $badTokens
     * @return string[]
     */
    private function trimStartingTokens(array $tokens, array $badTokens): array
    {
        while (in_array(Arrays::first($tokens), $badTokens)) {
            $tokens = Arrays::skip($tokens, 1);
        }

        return $tokens;
    }

    /**
     * Trims tokens from the end.
     *
     * @param string[] $tokens
     * @param string[] $badTokens
     * @return string[]
     */
    private function trimEndingTokens(array $tokens, array $badTokens): array
    {
        while (in_array(Arrays::last($tokens), $badTokens)) {
            $tokens = Arrays::trimTail($tokens, 1);
        }

        return $tokens;
    }

    /**
     * Returns trash tokens.
     *
     * These tokens are always filtered.
     *
     * @return string[]
     */
    protected function getTrashTokens(): array
    {
        return [
            'использовалось', 'использовалась', 'использовался', 'проиграла', 'проиграл', 'повторяю', 'например', 'говоришь', 'говорила', 'наверное', 'наверно', 'говорим', 'говорил', 'начинаю', 'понятно', 'конечно', 'давайте', 'скажите', 'сказали', 'сказала', 'сказал', 'говорю', 'говори', 'твоего', 'почему', 'вообще', 'кстати', 'короче', 'только', 'точнее', 'какой', 'какая', 'какие', 'какое', 'такой', 'такая', 'такую', 'такие', 'таких', 'такое', 'ладно', 'давай', 'тогда', 'вдруг', 'зачем', 'очень', 'опять', 'нихуя', 'снова', 'разве', 'чтобы', 'скажу', 'нужно', 'можно', 'твоих', 'твоей', 'блять', 'могут', 'твои', 'твоё', 'твое', 'твой', 'твоя', 'тебе', 'тебя', 'себе', 'себя', 'меня', 'туда', 'куда', 'тоже', 'сама', 'этот', 'хочу', 'нету', 'епта', 'ёпта', 'надо', 'всем', 'всех', 'было', 'была', 'алло', 'тут', 'был', 'эти', 'это', 'эта', 'так', 'как', 'сам', 'вот', 'уже', 'дай', 'вам', 'мне', 'мои', 'мой', 'моя', 'мою', 'мое', 'моё', 'еще', 'ещё', 'нет', 'бля', 'епт', 'ёпт', 'все', 'всё', 'наш', 'нас', 'там', 'где', 'ней', 'нем', 'нём', 'них', 'его', 'ему', 'тот', 'они', 'но', 'он', 'мы', 'вы', 'то', 'та', 'те', 'ты', 'их', 'ой', 'ох', 'ок', 'яй', 'ай', 'да', 'фу', 'ну', 'же', 'ее', 'её', 'ей', 'хм', 'ха', 'ах', 'эх', 'эй', 'я', 'э', 'а', '-', '=', '?'
        ];
    }

    /**
     * Returns semi-trash tokens.
     *
     * These tokens are allowed to be used in case of just one token,
     * but are filtered if there are more than one token.
     *
     * @return string[]
     */
    protected function getSemiTrashTokens(): array
    {
        return [
            'здравствуйте', 'неправильно', 'естественно', 'здравствуй', 'пожалуйста', 'погромче', 'немножко', 'немного', 'сказать', 'чуточку', 'который', 'которая', 'которое', 'которые', 'проверь', 'пойдем', 'пойдём', 'сейчас', 'запиши', 'нельзя', 'хорошо', 'громче', 'потише', 'нахрен', 'теперь', 'помоги', 'пиздец', 'всякое', 'разное', 'значит', 'лучше', 'пусть', 'потом', 'точно', 'играю', 'пошли', 'блядь', 'люблю', 'слово', 'тупой', 'тупая', 'шлюха', 'дурак', 'дура', 'даже', 'тише', 'типа', 'блин', 'есть', 'ага', 'оно', 'она', 'что', 'че', 'чё'
        ];
    }

    /**
     * Returns tokens that must be removed both from the start and the end.
     *
     * @return string[]
     */
    protected function getTrimTokens(): array
    {
        return [
            'или', 'и'
        ];
    }

    /**
     * Returns tokens that must be removed from the start.
     *
     * @return string[]
     */
    protected function getStartingTrashTokens(): array
    {
        return [
            'может'
        ];
    }

    /**
     * Returns tokens that must be removed from the end.
     *
     * @return string[]
     */
    protected function getEndingTrashTokens(): array
    {
        // all prepositions should be here
        return [
            'на', 'в', 'о'
        ];
    }

    /**
     * @param string[] $tokens
     */
    protected function rebuildFrom(array $tokens): string
    {
        return $this->tokenizer->join($tokens);
    }

    public function userState(): ?array
    {
        return $this->userState;
    }

    public function applicationState(): ?array
    {
        return $this->applicationState;
    }

    public function state(): ?array
    {
        return $this->hasUser()
            ? $this->userState
            : $this->applicationState;
    }

    public function gender(): int
    {
        return $this->gender;
    }

    public function attitude(): int
    {
        return $this->attitude;
    }

    public function isButtonPressed(): bool
    {
        return $this->isButtonPressed;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function var(string $name, $default = null)
    {
        $state = $this->state();

        if ($state === null) {
            return $default;
        }

        return $state[$name] ?? $default;
    }

    /**
     * Checks if the request matches any of the commands.
     */
    public function isAny(string ...$commands): bool
    {
        return in_array($this->originalCommand, $commands)
            || in_array($this->command, $commands);
    }

    /**
     * Checks if the request has any of the tokens.
     */
    public function hasAny(string ...$tokens): bool
    {
        foreach ($tokens as $token) {
            if (in_array($token, $this->dirtyTokens)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the request has all the tokens in any of the token sets.
     *
     * @param string[] $tokenSets
     */
    public function hasAnySet(array ...$tokenSets): bool
    {
        foreach ($tokenSets as $set) {
            if ($this->has(...$set)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the request has all the tokens.
     */
    public function has(string ...$tokens): bool
    {
        foreach ($tokens as $token) {
            if (!in_array($token, $this->dirtyTokens)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the request matches patterns such as "что такое *" and returns words
     * matched by asterisks.
     *
     * @return string[]|null
     */
    public function matches(string $pattern): ?array
    {
        return $this->matchesTokens($pattern, $this->dirtyTokens)
            ?? $this->matchesTokens($pattern, $this->tokens);
    }

    /**
     * @param string[] $tokens
     * @return string[]|null
     */
    private function matchesTokens(string $pattern, array $tokens): ?array
    {
        $patternTokens = $this->tokenizer->tokenize($pattern);

        if (count($tokens) !== count($patternTokens)) {
            return null;
        }

        $matches = [];

        for ($i = 0; $i < count($patternTokens); $i++) {
            $patternToken = $patternTokens[$i];
            $token = $tokens[$i];

            if ($patternToken === self::WILDCARD) {
                $matches[] = $token;
                continue;
            }

            if ($patternToken !== $token) {
                return null;
            }
        }

        return $matches;
    }
}
