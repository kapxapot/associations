<?php

namespace Brightwood\Translation\Interfaces;

interface DictionaryInterface
{
    /**
     * @return array<string, string|array>
     */
    public function definitions(): array;
}
