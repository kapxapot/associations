<?php

namespace App\Controllers;

use App\Services\GoogleAuthService;
use Exception;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GoogleAuthController extends Controller
{
    private GoogleAuthService $googleAuthService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->googleAuthService = $container->get(GoogleAuthService::class);
    }

    public function signIn(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $idToken = $this->extractIdToken($request);

            $signIn = $this->googleAuthService->signIn($idToken);

            $token = $signIn['token'];
            $user = $signIn['user'];
            $cookieKey = $signIn['cookie_key'];
            $expiresAt = $signIn['expires_at'];

            $cookie = sprintf(
                '%s=%s; Path=/; HttpOnly; SameSite=Lax; Expires=%s',
                rawurlencode($cookieKey),
                rawurlencode($token),
                gmdate('D, d M Y H:i:s T', strtotime($expiresAt))
            );

            $response = $response->withAddedHeader('Set-Cookie', $cookie);

            return Response::json(
                $response,
                [
                    'ok' => true,
                    'token' => $token,
                    'expires_at' => $expiresAt,
                    'user' => $user->serialize(),
                ]
            );
        } catch (Exception $ex) {
            return Response::json(
                $response->withStatus(400),
                [
                    'ok' => false,
                    'message' => $ex->getMessage(),
                ]
            );
        }
    }

    private function extractIdToken(ServerRequestInterface $request): string
    {
        $body = $request->getParsedBody();

        if (!is_array($body)) {
            return '';
        }

        return trim(strval($body['id_token'] ?? $body['credential'] ?? ''));
    }
}
