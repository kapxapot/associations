<?php

namespace App\Models\Traits;

/**
 * @property string|null $meta
 */
trait Meta
{
    private ?array $metaData = null;
    private bool $metaInitialized = false;

    public function metaData(): array
    {
        $this->initMeta();

        return $this->metaData;
    }

    /**
     * @return mixed Returns `$default` (`null` by default) value if the value is not set.
     */
    public function getMetaValue(string $field, $default = null)
    {
        $this->initMeta();

        return $this->metaData[$field] ?? $default;
    }

    /**
     * @param mixed $value
     */
    public function setMetaValue(string $field, $value): void
    {
        $this->initMeta();

        $this->metaData[$field] = $value;
    }

    private function initMeta(): void
    {
        if ($this->metaInitialized) {
            return;
        }

        if ($this->metaData === null && strlen($this->meta) > 0) {
            $this->metaData = json_decode($this->meta, true);
        }

        $this->metaData ??= [];

        $this->metaInitialized = true;
    }
}
