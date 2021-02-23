<?php

namespace App\Answers\Alice;

use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Util\Text;

abstract class AbstractAnswerer
{
    protected const COMMAND_HELP = 'помощь';
    protected const COMMAND_CAN = 'что ты умеешь';

    protected const MESSAGE_EMPTY_QUESTION = 'Извините, не поняла';
    protected const MESSAGE_WELCOME = 'Привет! Поиграем в Ассоциации? Говорим по очереди слово, которое ассоциируется с предыдущим. Я начинаю:';
    protected const MESSAGE_WELCOME_BACK = 'С возвращением! Я продолжаю:';
    protected const MESSAGE_HELP = 'В игре в ассоциации Алиса и игрок говорят по очереди слово, которое ассоциируется с предыдущим. Желательно использовать существительные. Скажите \'дальше\' или \'пропустить\', если не хотите отвечать на слово. Продолжаем. Мое слово:';
    protected const MESSAGE_SKIP = 'Хорошо.';
    protected const MESSAGE_START_ANEW = 'Начинаем заново:';
    protected const MESSAGE_ERROR = 'Что-то пошло не так';

    protected WordRepositoryInterface $wordRepository;
    protected LanguageService $languageService;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService
    )
    {
        $this->wordRepository = $wordRepository;
        $this->languageService = $languageService;
    }

    protected function getLanguage(): Language
    {
        return $this->languageService->getDefaultLanguage();
    }

    protected function renderWord(?Word $word): string
    {
        return $word !== null
            ? $this->renderWordStr($word->word)
            : 'У меня нет слов';
    }

    protected function emptyQuestionResponse(): AliceResponse
    {
        return $this->buildResponse(self::MESSAGE_EMPTY_QUESTION);
    }

    protected function buildResponse(?string ...$parts): AliceResponse
    {
        return new AliceResponse(
            Text::join(array_filter($parts), ' ')
        );
    }

    protected function isHelpCommand(AliceRequest $request): bool
    {
        return $request->isAny(
            self::COMMAND_HELP,
            self::COMMAND_CAN
        );
    }

    protected function isSkipCommand(AliceRequest $request): bool
    {
        $skipPhrases = [
            'другое слово',
            'я в тупике',
            'я не знаю',
            'не знаю',
        ];

        $tokens = [
            'сдаюсь',
            'пропусти',
            'пропустить',
            'продолжить',
            'продолжаем',
            'продолжим',
            'продолжай',
            'дальше',
            'заново',
        ];

        return $request->isAny(...$skipPhrases)
            || $request->hasAnyToken(...$tokens);
    }

    protected function matureWordMessage(): string
    {
        return $this->randomString(
            'Ой! Надеюсь, рядом нет детей.',
            'Вы вгоняете меня в краску.',
            'Ну у вас и словечки!',
            'Хм... Как скажете.'
        );
    }

    protected function noAssociationMessage(): string
    {
        return $this->randomString(
            'У меня нет ассоциаций.',
            'Мне нечего сказать.',
            'Я в тупике.',
            'Я сдаюсь.'
        );
    }

    protected function randomString(string ...$strings): ?string
    {
        return StringCollection::make($strings)->random();
    }

    protected function findWord(?string $wordStr): ?Word
    {
        $language = $this->getLanguage();
        $wordStr = $this->languageService->normalizeWord($language, $wordStr);

        return $this->wordRepository->findInLanguage($language, $wordStr);
    }

    protected function renderWordStr(string $word): string
    {
        return mb_strtoupper($word);
    }
}
