<?php

namespace App\Models\Interfaces;

interface ActorInterface extends GenderedInterface
{
    public function languageCode(): ?string; 
}
