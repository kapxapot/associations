<?php

namespace App\Controllers;

use App\Answers\Alice\ApplicationAnswerer;
use App\Answers\Alice\UserAnswerer;
use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
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

    public function __construct(
        ApplicationAnswerer $applicationAnswerer,
        UserAnswerer $userAnswerer,
        AliceUserService $aliceUserService,
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger
    )
    {
        $this->applicationAnswerer = $applicationAnswerer;
        $this->userAnswerer = $userAnswerer;

        $this->aliceUserService = $aliceUserService;

        $this->settingsProvider = $settingsProvider;
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
                $this->buildMessage($aliceResponse)
            );
        } catch (Exception $ex) {
            $this->logEx($ex);
        }

        return Response::text($response, 'Error');
    }

    private function getResponse(AliceRequest $request): AliceResponse
    {
        $aliceUser = $request->hasUser()
            ? $this->aliceUserService->getOrCreateAliceUser($request)
            : null;

        return ($aliceUser === null)
            ? $this->applicationAnswerer->getResponse($request)
            : $this->userAnswerer->getResponse($request, $aliceUser);
    }

    private function buildMessage(AliceResponse $response): array
    {
        $data = [
            'response' => [
                'text' => $response->text,
                'end_session' => $response->endSession,
            ],
            'version' => '1.0',
        ];

        $data['user_state_update'] = $response->userState ?? new stdClass();
        $data['application_state'] = $response->applicationState ?? new stdClass();

        return $data;
    }
}
