<?php

namespace App\Bots\Factories;

use App\Bots\Command;
use App\Bots\MessageRenderer;
use Plasticode\Util\Classes;

class BotMessageRendererFactory
{
    public function __invoke()
    {
        $renderer = new MessageRenderer();

        return $renderer
            ->withHandlers([
                'cmd' => function (string $text) {
                    $commands = Classes::getConstants(Command::class);

                    $commandName = mb_strtoupper($text);
                    $commandText = $commands[$commandName] ?? $text;

                    return '«' . $commandText . '»';
                },
                'q' => fn (string $text) => '«' . $text . '»',
            ]);
    }
}
