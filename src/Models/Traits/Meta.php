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
    public function getMetaValue(string $key, $default = null)
    {
        $this->initMeta();
        return $this->metaData[$key] ?? $default;
    }

    /**
     * Sets several meta values at once. If a value is `null`, the corresponding meta key is deleted.
     *
     * @return $this
     */
    public function setMeta(array $meta): self
    {
        $this->initMeta();

        foreach ($meta as $key => $value) {
            $this->setMetaValue($key, $value);
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setMetaValue(string $key, $value): void
    {
        $this->initMeta();

        if ($this->getMetaValue($key) !== $value) {
            if ($value === null) {
                $this->deleteMetaValue($key);
            } else {
                $this->metaData[$key] = $value;
            }
        }
    }

    public function deleteMetaValue(string $key): void
    {
        $this->initMeta();

        if (array_key_exists($key, $this->metaData)) {
            unset($this->metaData[$key]);
        }
    }

    public function encodeMeta(): ?string
    {
        return empty($this->metaData)
            ? null
            : json_encode($this->metaData(), JSON_UNESCAPED_UNICODE);
    }

    private function initMeta(): void
    {
        if ($this->metaInitialized) {
            return;
        }

        $this->metaData = $this->decodeMeta() ?? [];
        $this->metaInitialized = true;
    }

    private function decodeMeta(): ?array
    {
        return $this->metaData === null && strlen($this->meta) > 0
            ? json_decode($this->meta, true)
            : null;
    }
}
