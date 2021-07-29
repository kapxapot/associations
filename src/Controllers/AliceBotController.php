<?php

namespace App\Controllers;

use App\Bots\AliceRequest;
use App\Bots\Answerers\ApplicationAnswerer;
use App\Bots\Answerers\UserAnswerer;
use App\Bots\BotResponse;
use App\Bots\Factories\BotMessageRendererFactory;
use App\Bots\Interfaces\MessageRendererInterface;
use App\Services\AliceUserService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use stdClass;

class AliceBotController
{
    use LoggerAwareTrait;

    private ApplicationAnswerer $applicationAnswerer;
    private UserAnswerer $userAnswerer;

    private AliceUserService $aliceUserService;

    private SettingsProviderInterface $settingsProvider;

    private MessageRendererInterface $messageRenderer;

    public function __construct(
        ApplicationAnswerer $applicationAnswerer,
        UserAnswerer $userAnswerer,
        AliceUserService $aliceUserService,
        SettingsProviderInterface $settingsProvider,
        BotMessageRendererFactory $messageRendererFactory,
        LoggerInterface $logger
    )
    {
        $this->applicationAnswerer = $applicationAnswerer;
        $this->userAnswerer = $userAnswerer;

        $this->aliceUserService = $aliceUserService;

        $this->settingsProvider = $settingsProvider;

        $this->messageRenderer = ($messageRendererFactory)();

        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            if (!empty($data)) {
                $logEnabled = $this->settingsProvider->get('alice.bot_log', false);

                if ($logEnabled === true) {
                    $this->log('Got request', $data);
                }
            }

            $aliceRequest = new AliceRequest($data);
            $aliceResponse = $this->getResponse($aliceRequest);

            return Response::json(
                $response,
                $this->buildMessage($aliceRequest, $aliceResponse)
            );
        } catch (Exception $ex) {
            $this->logEx($ex);
        }

        return Response::text($response, 'Error');
    }

    private function getResponse(AliceRequest $request): BotResponse
    {
        $aliceUser = $request->hasUser()
            ? $this->aliceUserService->getOrCreateAliceUser($request)
            : null;

        return ($aliceUser === null)
            ? $this->applicationAnswerer->getResponse($request)
            : $this->userAnswerer->getResponse($request, $aliceUser);
    }

    private function buildMessage(AliceRequest $request, BotResponse $response): array
    {
        $gender = $request->gender();
        $attitude = $request->attitude();

        $text = $this
            ->messageRenderer
            ->withGender($gender)
            ->withVars([
                'att' => $attitude,
                'hello' => 'Привет',
                'word_limit' => UserAnswerer::WORD_LIMIT,
            ])
            ->render($response->text());

        $data = [
            'response' => [
                'text' => $text,
                'end_session' => $response->endSession(),
            ],
            'version' => '1.0',
        ];

        $data['user_state_update'] = $response->userState() ?? new stdClass();
        $data['application_state'] = $response->applicationState() ?? new stdClass();

        return $data;
    }
}
