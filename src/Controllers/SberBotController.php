<?php

namespace App\Controllers;

use App\Bots\Answerers\ApplicationAnswerer;
use App\Bots\Answerers\UserAnswerer;
use App\Bots\BotResponse;
use App\Bots\Command;
use App\Bots\Factories\BotMessageRendererFactory;
use App\Bots\Interfaces\MessageRendererInterface;
use App\Bots\SberRequest;
use App\Semantics\SentenceCleaner;
use App\Services\SberUserService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Semantics\Attitude;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SberBotController
{
    use LoggerAwareTrait;

    private ApplicationAnswerer $applicationAnswerer;
    private UserAnswerer $userAnswerer;

    private SberUserService $sberUserService;

    private SettingsProviderInterface $settingsProvider;

    private MessageRendererInterface $messageRenderer;

    private SentenceCleaner $sentenceCleaner;

    private bool $logEnabled;

    public function __construct(
        ApplicationAnswerer $applicationAnswerer,
        UserAnswerer $userAnswerer,
        SberUserService $sberUserService,
        SettingsProviderInterface $settingsProvider,
        BotMessageRendererFactory $messageRendererFactory,
        LoggerInterface $logger,
        SentenceCleaner $sentenceCleaner
    )
    {
        $this->applicationAnswerer = $applicationAnswerer;
        $this->userAnswerer = $userAnswerer;

        $this->sberUserService = $sberUserService;

        $this->settingsProvider = $settingsProvider;

        $this->messageRenderer = ($messageRendererFactory)();

        $this->logger = $logger;
        $this->logEnabled = $this->settingsProvider->get('sber.bot_log', false) === true;

        $this->sentenceCleaner = $sentenceCleaner;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            if (!empty($data) && $this->logEnabled) {
                $this->log('Got request', $data);
            }

            $sberRequest = new SberRequest($data);
            $sberResponse = $this->getResponse($sberRequest);

            $answer = $this->buildMessage($sberRequest, $sberResponse);

            if ($this->logEnabled) {
                $this->log('Answer', $answer);
            }

            return Response::json(
                $response,
                $answer
            );
        } catch (Exception $ex) {
            $this->logEx($ex);
        }

        return Response::text($response, 'Error');
    }

    private function getResponse(SberRequest $request): BotResponse
    {
        $sberUser = $request->hasUser()
            ? $this->sberUserService->getOrCreateSberUser($request)
            : null;

        return ($sberUser === null)
            ? $this->applicationAnswerer->getResponse($request)
            : $this->userAnswerer->getResponse($request, $sberUser);
    }

    private function buildMessage(SberRequest $request, BotResponse $response): array
    {
        $gender = $request->gender();
        $attitude = $request->attitude();

        $text = $this
            ->messageRenderer
            ->withGender($gender)
            ->withVars([
                'att' => $attitude,
                'hello' => $attitude == Attitude::OFFICIAL ? 'Здравствуйте' : 'Привет',
                'word_limit' => UserAnswerer::WORD_LIMIT,
            ])
            ->render($response->text());

        // special trailing dor trimming for Sber
        $text = $this->sentenceCleaner->trimTrailingDot($text);

        $data = [
            'messageName' => 'ANSWER_TO_USER',
            'sessionId' => $request->sessionId,
            'messageId' => $request->messageId,
            'uuid' => $request->uuid,
            'payload' => [
                'device' => $request->device,
                'finished' => $response->endSession(),
                'pronounceText' => $text,
                'items' => [
                    [
                        'bubble' => [
                            'text' => $text,
                            'expand_policy' => 'force_expand',
                        ]
                    ],
                ],
            ]
        ];

        if ($response->hasState()) {
            $state = [];

            if (!empty($response->userState())) {
                $state[SberRequest::USER_STATE] = $response->userState();
            }

            if (!empty($response->applicationState())) {
                $state[SberRequest::APPLICATION_STATE] = $response->applicationState();
            }

            $data['payload'][SberRequest::STATE_ROOT] = json_encode($state);
        }

        if ($response->hasActions()) {
            $buttons = array_map(
                fn (string $a) => [
                    'title' => Command::getLabel($a),
                    'actions' => [
                        $this->commandAsServerAction($a),
                    ]
                ],
                $response->actions()
            );

            $data['payload']['suggestions'] = ['buttons' => $buttons];
        }

        return $data;
    }

    private function commandAsTextAction(string $command): array
    {
        return [
            'text' => Command::getLabel($command),
            'type' => 'text'
        ];
    }

    private function commandAsServerAction(string $command): array
    {
        return [
            'type' => 'server_action',
            'message_name' => SberRequest::SERVER_ACTION,
            'server_action' => [
                'action_id' => $command,
            ]
        ];
    }
}
