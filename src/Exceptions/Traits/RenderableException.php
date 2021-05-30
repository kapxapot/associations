<?php

namespace App\Exceptions\Traits;

trait RenderableException
{
    private array $params = [];

    /**
     * @param mixed $param
     */
    protected function addParam($param): void
    {
        $this->params[] = $param;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    abstract protected function getMessageTemplate(): string;

    public function getRenderedMessage(): string
    {
        return sprintf(
            $this->getMessageTemplate(),
            ...$this->getParams()
        );
    }
}
