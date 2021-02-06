<?php

namespace App\External\Interfaces;

use App\Models\DTO\DefinitionData;

interface DefinitionSourceInterface
{
    public function request(string $languageCode, string $word): DefinitionData;
}
