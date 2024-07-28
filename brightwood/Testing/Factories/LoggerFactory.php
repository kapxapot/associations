<?php

namespace Brightwood\Testing\Factories;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    public static function make(): LoggerInterface
    {
        $logger = new Logger('brightwood-test');

        $handler = new StreamHandler(
            'logs\brightwood_test.log',
            Logger::DEBUG
        );

        $formatter = new LineFormatter(
            null, null, false, true
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
    
        return $logger;
    }
}
