<?php

namespace Brightwood\Testing\Mocks;

use Brightwood\Translation\Interfaces\DictionaryInterface;

class DictionaryMock implements DictionaryInterface
{
    private array $definitions = [];

    public function __construct(?array $definitions = null)
    {
        if ($definitions) {
            $this->definitions = $definitions;
        }
    }

    public function definitions(): array
    {
        return $this->definitions;
    }
}
