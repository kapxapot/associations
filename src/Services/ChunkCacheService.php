<?php

namespace App\Services;

class ChunkCacheService
{
    private const BASE_TTL_SECONDS = 24 * 60 * 60;
    private const TTL_JITTER_SECONDS = 4 * 60 * 60;

    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir
            ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'associations_chunk_cache';
    }

    public function remember(string $key, callable $producer): string
    {
        $cached = $this->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $content = $producer();
        $this->put($key, $content);

        return $content;
    }

    private function get(string $key): ?string
    {
        $path = $this->pathByKey($key);

        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);

        if (
            !is_array($data)
            || !isset($data['expires_at'])
            || !isset($data['content'])
            || $data['expires_at'] <= time()
        ) {
            @unlink($path);
            return null;
        }

        return strval($data['content']);
    }

    private function put(string $key, string $content): void
    {
        $this->ensureCacheDir();

        $payload = json_encode(
            [
                'expires_at' => time() + $this->ttlWithJitter(),
                'content' => $content,
            ],
            JSON_UNESCAPED_UNICODE
        );

        if ($payload === false) {
            return;
        }

        @file_put_contents($this->pathByKey($key), $payload, LOCK_EX);
    }

    private function ttlWithJitter(): int
    {
        return self::BASE_TTL_SECONDS
            + random_int(-self::TTL_JITTER_SECONDS, self::TTL_JITTER_SECONDS);
    }

    private function ensureCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
    }

    private function pathByKey(string $key): string
    {
        return $this->cacheDir
            . DIRECTORY_SEPARATOR
            . sha1($key)
            . '.json';
    }
}
