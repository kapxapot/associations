<?php

namespace Brightwood\Translation\Interfaces;

interface TranslatorFactoryInterface
{
    public function __invoke(string $langCode): TranslatorInterface;
}
