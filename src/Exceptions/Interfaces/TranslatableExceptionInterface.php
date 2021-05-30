<?php

namespace App\Exceptions\Interfaces;

use Plasticode\Core\Interfaces\TranslatorInterface;

interface TranslatableExceptionInterface extends RenderableExceptionInterface
{
    public function getTranslatedMessage(TranslatorInterface $translator): string;
}
