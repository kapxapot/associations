<?php

namespace App\Exceptions\Traits;

use Plasticode\Core\Interfaces\TranslatorInterface;

trait TranslatableException
{
    use RenderableException;

    public function getTranslatedMessage(TranslatorInterface $translator): string
    {
        return sprintf(
            $translator->translate($this->getMessageTemplate()),
            ...$this->getParams()
        );
    }
}
