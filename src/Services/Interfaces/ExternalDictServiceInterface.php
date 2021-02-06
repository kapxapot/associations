<?php

namespace App\Services\Interfaces;

use App\Models\Language;
use App\Models\Interfaces\DictWordInterface;

interface ExternalDictServiceInterface
{
    function loadFromDictionary(
        Language $language,
        string $wordStr
    ): ?DictWordInterface;
}
