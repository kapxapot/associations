<?php

namespace Brightwood\Models\Data;

class JsonStoryData extends StoryData
{
    private array $jsonData;

    public function __construct(?array $jsonData = null, ?array $data = null)
    {
        $this->jsonData = $jsonData ?? [];

        parent::__construct($data);
    }

    protected function init(): void
    {
        $this->data = $this->jsonData['init'] ?? [];
    }

    /**
     * @return $this
     */
    protected function applyEffect(string $name): self
    {
        $effect = $this->jsonData['effects'][$name] ?? null;

        if ($effect) {
            // apply the effect...
        }

        return $this;
    }
}
