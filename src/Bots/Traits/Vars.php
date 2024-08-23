<?php

namespace App\Bots\Traits;

trait Vars
{
    /** @var array<string, mixed> */
    protected array $vars = [];

    public function vars(): array
    {
        return $this->vars;
    }

    /**
     * @return $this
     */
    public function withVars(?array $vars = null): self
    {
        if (!empty($vars)) {
            foreach ($vars as $name => $value) {
                $this->withVar($name, $value);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withVar(string $name, $value): self
    {
        $this->vars[$name] = $value;
        return $this;
    }

    protected function hasVar(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }
}
