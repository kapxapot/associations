<?php

namespace App\External;

use App\Models\DTO\DefinitionData;

interface DefinitionSourceInterface
{
    public function request(string $languageCode, string $word): DefinitionData;
}
