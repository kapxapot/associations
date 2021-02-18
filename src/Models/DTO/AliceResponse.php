<?php

namespace App\Models\DTO;

class AliceResponse
{
    public ?string $text;
    public bool $endSession = false;
    public ?array $userState = null;
    public ?array $applicationState = null;

    public function __construct(
        ?string $text
    )
    {
        $this->text = $text;
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
     * @return $this
     */
    public function withUserState(array $userState): self
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
    public function withApplicationState(array $applicationState): self
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
