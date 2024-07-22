<?php

namespace Brightwood;

class JsonDataLoader
{
    public static function load(string $fileName): array
    {
        $json = file_get_contents($fileName);
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
