<?php

namespace App\Models\Interfaces;

interface ActorInterface extends GenderedInterface
{
    public function hasLanguageCode(): bool;

    public function languageCode(): ?string;
}
