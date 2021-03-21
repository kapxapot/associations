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
    protected const VAR_STATE = 'state';
    protected const VAR_COMMAND = 'command';

    protected const STATE_HELP = 'help';
    protected const STATE_RULES = 'rules';
    protected const STATE_COMMANDS = 'commands';
    protected const STATE_COMMAND_CONFIRM = 'command_confirm';

    protected const COMMAND_HELP = 'помощь';
    protected const COMMAND_RULES = 'правила';
    protected const COMMAND_COMMAND = 'команда';
    protected const COMMAND_COMMANDS = 'команды';
    protected const COMMAND_PLAYING = 'играем';

    protected const MESSAGE_CLUELESS = 'Извините, не поняла';
    protected const MESSAGE_WELCOME = 'Привет! Поиграем в ассоциации?';
    protected const MESSAGE_WELCOME_BACK = 'С возвращением!';

    protected const CHUNK_RULES = 'Чтобы узнать, как играть, скажите \'правила\'.';
    protected const CHUNK_COMMANDS = 'Чтобы узнать, как управлять игрой, скажите \'команды\'.';
    protected const CHUNK_PLAY = 'Чтобы перейти к игре, скажите \'играть\'.';

    protected const MESSAGE_DEMO = 'Игра идет в демо-режиме. Для полной игры, пожалуйста, авторизуйтесь.';

    private const MESSAGE_RULES_COMMON = 'В игре в ассоциации мы с вами говорим по очереди слово, которое ассоциируется с предыдущим. Например, я говорю \'лес\', вы отвечаете \'заяц\', я говорю \'морковка\' и т.д.';

    protected const MESSAGE_RULES_APPLICATION = self::MESSAGE_RULES_COMMON . ' Лучше использовать существительные.';

    protected const MESSAGE_RULES_USER = self::MESSAGE_RULES_COMMON . ' При этом игра запоминает ваши слова и ассоциации и учится на них. Лучше использовать существительные.';

    protected const MESSAGE_COMMANDS_APPLICATION = 'Для пропуска слова скажите \'другое слово\' или \'дальше\'. Для выхода из игры скажите \'хватит\'.';

    protected const MESSAGE_COMMANDS_USER = 'Для пропуска слова скажите \'другое слово\' или \'дальше\'. Для повтора последнего слова скажите \'повтори\'. Спросите \'что?\' или \'что это?\', чтобы узнать значение слова. Если вам не нравится слово или ассоциация, скажите \'плохое слово\' или \'плохая ассоциация\'. Для выхода из игры скажите \'хватит\'.';

    protected const MESSAGE_SKIP = 'Хорошо.';
    protected const MESSAGE_START_ANEW = 'Начинаем заново:';
    protected const MESSAGE_ERROR = 'Что-то пошло не так';
    protected const MESSAGE_CONTINUE = 'Продолжаем. Мое слово:';

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

    protected function cluelessResponse(): AliceResponse
    {
        return $this->buildResponse(self::MESSAGE_CLUELESS);
    }

    /**
     * @param string[]|string|null $parts
     */
    protected function buildResponse(...$parts): AliceResponse
    {
        $lines = [];

        foreach ($parts as $part) {
            if ($part === null) {
                continue;
            }

            if (is_array($part)) {
                $lines = array_merge($lines, $part);
            } else {
                $lines[] = $part;
            }
        }

        return new AliceResponse(
            Text::join($lines, ' ')
        );
    }

    protected function isHelpDialog(AliceRequest $request): bool
    {
        $state = $request->var(self::VAR_STATE);

        $helpStates = [
            self::STATE_HELP,
            self::STATE_RULES,
            self::STATE_COMMANDS,
        ];

        return in_array($state, $helpStates);
    }

    protected function isHelpRulesCommand(AliceRequest $request): bool
    {
        return $request->hasAnySet(
            [self::COMMAND_RULES],
            ['правила', 'игры'],
            ['как', 'играть']
        );
    }

    protected function isHelpCommandsCommand(AliceRequest $request): bool
    {
        return $request->hasAny(
            self::COMMAND_COMMANDS,
            'команда'
        );
    }

    protected function isPlayCommand(AliceRequest $request): bool
    {
        return $request->hasAny(
            'играть',
            'игра',
            'играю',
            self::COMMAND_PLAYING
        );
    }

    public function helpCommand(
        AliceRequest $request,
        string ...$prependMessages
    ): AliceResponse
    {
        return $this
            ->buildResponse(
                $prependMessages,
                self::CHUNK_RULES,
                self::CHUNK_COMMANDS,
                self::CHUNK_PLAY
            )
            ->withVarBy($request, self::VAR_STATE, self::STATE_HELP);
    }

    protected function isNativeAliceCommand(AliceRequest $request): bool
    {
        return $request->hasAny(
            'включи',
            'включить',
            'выйти',
            'выключи',
            'выключись',
            'выключить',
            'выходить',
            'заканчиваем',
            'закончили',
            'закончить',
            'запусти',
            'запустить',
            'играй',
            'надоело',
            'отключи',
            'отключись',
            'отключить',
            'открой',
            'открыть',
            'пока',
            'покажи',
            'прекрати',
            'прекратить',
            'прекращай',
            'прекращаем',
            'скажи'
        ) || $request->hasAnySet(
            ['стоп', 'игра'],
            ['конец', 'игры']
        );
    }

    protected function isWhatCommand(AliceRequest $request): bool
    {
        return $request->hasAny('кто', 'что', 'чего')
            || $request->hasAnySet(
                ['не', 'понял'],
                ['не', 'поняла'],
                ['не', 'понятно']
            );
    }

    protected function isRepeatCommand(AliceRequest $request): bool
    {
        return $request->hasAny(
            'играть',
            'играем',
            'повтори'
        ) || $request->hasAnySet(
            ['еще', 'раз'],
            ['не', 'расслышал'],
            ['не', 'расслышала'],
            ['не', 'слышно']
        );
    }

    protected function isHelpCommand(AliceRequest $request): bool
    {
        return $request->isAny(
            self::COMMAND_HELP,
            'что ты умеешь'
        );
    }

    protected function isSkipCommand(AliceRequest $request): bool
    {
        return $request->hasAny(
            'сдаюсь',
            'пропусти',
            'пропускай',
            'пропускаю',
            'пропускаем',
            'пропустить',
            'продолжи',
            'продолжим',
            'продолжай',
            'продолжить',
            'продолжаем',
            'дальше',
            'заново',
            'сначала',
            'начинай',
            'начни',
            'начинаем',
            'следующая',
            'следующее',
            'следующий'
        )
        || $request->hasAnySet(
            ['нет', 'слов'],
            ['нет', 'ассоциации'],
            ['нет', 'ассоциаций'],
            ['нечего', 'сказать'],
            ['не', 'знаю'],
            ['в', 'тупике'],
            ['другое', 'слово'],
        );
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

    /**
     * @return string[]
     */
    protected function getKnownVars(): array
    {
        return [
            self::VAR_STATE,
            self::VAR_COMMAND,
        ];
    }
}
