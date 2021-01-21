<?php

namespace App\Traits;

use Psr\Log\LoggerInterface;

trait Logging
{
    private ?LoggerInterface $logger;

    private function log(string $message, ?array $data = null): void
    {
        if (is_null($this->logger)) {
            return;
        }

        $this->logger->info($message, $data);
    }
}
