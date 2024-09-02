<?php

namespace Brightwood\Answers;

use App\Models\TelegramUser;
use Brightwood\Factories\TelegramTransportFactory;
use Brightwood\Parsing\StoryParser;
use Brightwood\Repositories\Interfaces\StoryStatusRepositoryInterface;
use Brightwood\Services\StoryService;
use Brightwood\Services\TelegramUserService;
use Psr\Log\LoggerInterface;

class AnswererFactory
{
    /** @var callable */
    private $invoker;

    public function __construct(
        LoggerInterface $logger,
        StoryStatusRepositoryInterface $storyStatusRepository,
        StoryService $storyService,
        StoryParser $parser,
        TelegramUserService $telegramUserService,
        TelegramTransportFactory $telegramFactory,
        UrlBuilder $urlBuilder
    )
    {
        $this->invoker = fn (TelegramUser $tgUser, string $tgLangCode) => new Answerer(
            $logger,
            $storyStatusRepository,
            $storyService,
            $parser,
            $telegramUserService,
            $telegramFactory,
            $urlBuilder,
            $tgUser,
            $tgLangCode
        );
    }

    public function __invoke(TelegramUser $tgUser, string $tgLangCode): Answerer
    {
        return ($this->invoker)($tgUser, $tgLangCode);
    }
}
