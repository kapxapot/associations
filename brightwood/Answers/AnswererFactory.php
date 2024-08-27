<?php

namespace Brightwood\Answers;

use App\Core\Interfaces\LinkerInterface;
use App\Models\TelegramUser;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Log\LoggerInterface;

class AnswererFactory
{
    /** @var callable */
    private $invoker;

    public function __construct(
        LoggerInterface $logger,
        SettingsProviderInterface $settingsProvider,
        LinkerInterface $linker,
        StoryStatusRepositoryInterface $storyStatusRepository,
        StoryService $storyService,
        StoryParser $parser,
        TelegramTransportFactory $telegramFactory
    )
    {
        $this->invoker = fn (TelegramUser $tgUser, string $tgLangCode) => new Answerer(
            $logger,
            $settingsProvider,
            $linker,
            $storyStatusRepository,
            $storyService,
            $parser,
            $telegramFactory,
            $tgUser,
            $tgLangCode
        );
    }

    public function __invoke(TelegramUser $tgUser, string $tgLangCode): Answerer
    {
        return ($this->invoker)($tgUser, $tgLangCode);
    }
}
