<?php

namespace App\Controllers;

use App\Bots\Sber\SberRequest;
use App\Bots\Sber\SberResponse;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SberBotController
{
    use LoggerAwareTrait;

    private SettingsProviderInterface $settingsProvider;

    private bool $logEnabled;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        LoggerInterface $logger
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->logger = $logger;

        $this->logEnabled = $this->settingsProvider->get('sber.bot_log', false);
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

    private function getResponse(SberRequest $request): SberResponse
    {
        // $sberUser = $request->hasUser()
        //     ? $this->sberUserService->getOrCreateSberUser($request)
        //     : null;

        // return ($sberUser === null)
        //     ? $this->applicationAnswerer->getResponse($request)
        //     : $this->userAnswerer->getResponse($request, $sberUser);

        return new SberResponse('test');
    }

    private function buildMessage(SberRequest $request, SberResponse $response): array
    {
        $data = [
            'messageName' => 'ANSWER_TO_USER',
            'sessionId' => $request->sessionId,
            'messageId' => $request->messageId,
            'uuid' => $request->uuid,
            'payload' => [
                'device' => $request->device,
                'pronounceText' => $response->text,
                'items' => [
                    [
                        'bubble' => [
                            'text' => $response->text,
                        ]
                    ],
                ],
            ]
        ];

        return $data;
    }
}
