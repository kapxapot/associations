<?php

namespace Brightwood\Translation\Interfaces;

interface TranslatorInterface
{
    public function translate(string $key): string;
}
