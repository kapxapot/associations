<?php

namespace Brightwood\Models\Stories\Core;

class JsonFileStory extends JsonStory
{
    public function __construct(int $id, string $fileName, bool $published = false)
    {
        $jsonStr = file_get_contents($fileName);
        $jsonData = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);

        parent::__construct($id, $jsonData, $published);
    }
}
