<?php

namespace App\Bots;

use Plasticode\Util\Text;

class BotResponse
{
    /** @var string[] */
    protected array $lines;

    protected string $text;

    protected bool $endSession = false;

    protected ?array $userState = null;
    protected ?array $applicationState = null;

    public function __construct(string ...$lines)
    {
        $this->lines = $lines;

        $this->text = Text::join($lines, ' ');
    }

    /**
     * @return string[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function endSession(): bool
    {
        return $this->endSession;
    }

    public function hasState(): bool
    {
        return !empty($this->state());
    }

    public function state(): ?array
    {
        return $this->userState() ?? $this->applicationState();
    }

    public function userState(): ?array
    {
        return $this->userState;
    }

    public function applicationState(): ?array
    {
        return $this->applicationState;
    }

    /**
     * @return $this
     */
    public function withEndSession(bool $endSession): self
    {
        $this->endSession = $endSession;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function withVarBy(AbstractBotRequest $request, string $name, $value): self
    {
        return $request->hasUser()
            ? $this->withUserVar($name, $value)
            : $this->withApplicationVar($name, $value);
    }

    /**
     * @return $this
     */
    public function withStateFrom(AbstractBotRequest $request): self
    {
        return $request->hasUser()
            ? $this->withUserState($request->userState())
            : $this->withApplicationState($request->applicationState());
    }

    /**
     * @return $this
     */
    public function withUserState(?array $userState): self
    {
        $this->userState = $userState;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function withUserVar(string $name, $value): self
    {
        $this->userState ??= [];

        $this->userState[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function withApplicationState(?array $applicationState): self
    {
        $this->applicationState = $applicationState;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function withApplicationVar(string $name, $value): self
    {
        $this->applicationState ??= [];

        $this->applicationState[$name] = $value;

        return $this;
    }
}
