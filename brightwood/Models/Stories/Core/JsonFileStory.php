<?php

namespace Brightwood\Models\Stories\Core;

class JsonFileStory extends JsonStory
{
    private string $json;

    public function __construct(int $id, string $fileName)
    {
        $jsonDir = __DIR__ . '/../Json/';

        $this->json = file_get_contents($jsonDir . $fileName);
        $jsonData = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);

        parent::__construct($id, $jsonData);
    }

    public function json(): string
    {
        return $this->json;
    }
}
