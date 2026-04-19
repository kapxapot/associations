<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use DateInterval;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class GoogleAuthService
{
    private const TOKEN_LENGTH = 16;

    private Client $httpClient;
    private SettingsProviderInterface $settingsProvider;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        Client $httpClient,
        SettingsProviderInterface $settingsProvider,
        UserRepositoryInterface $userRepository
    )
    {
        $this->httpClient = $httpClient;
        $this->settingsProvider = $settingsProvider;
        $this->userRepository = $userRepository;
    }

    public function signIn(string $idToken): array
    {
        $payload = $this->verifyIdToken($idToken);

        $user = $this->resolveUser($payload);

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        $expiresAt = $this->buildExpiresAt();

        \ORM::for_table('auth_tokens')
            ->create([
                'user_id' => $user->getId(),
                'token' => $token,
                'expires_at' => $expiresAt,
            ])
            ->save();

        $cookieKey = $this->settingsProvider->get('auth_token_key');

        return [
            'token' => $token,
            'user' => $user,
            'expires_at' => $expiresAt,
            'cookie_key' => $cookieKey,
        ];
    }

    private function buildExpiresAt(): string
    {
        $ttlHours = intval($this->settingsProvider->get('token_ttl') ?? 168);

        $expiresAt = (new DateTimeImmutable())
            ->add(new DateInterval('PT' . $ttlHours . 'H'));

        return $expiresAt->format('Y-m-d H:i:s');
    }

    private function resolveUser(array $payload): User
    {
        $googleId = strval($payload['sub'] ?? '');

        if (strlen($googleId) === 0) {
            throw new Exception('Google user id is missing.');
        }

        $row = \ORM::for_table('users')
            ->where('google_id', $googleId)
            ->find_one();

        if ($row !== false) {
            return $this->userRepository->get(intval($row->id));
        }

        $email = strval($payload['email'] ?? '');

        $login = $this->buildUniqueLogin($email, $googleId);

        $name = strval($payload['name'] ?? '');

        return $this->userRepository->store([
            'login' => $login,
            'password' => password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT),
            'name' => strlen($name) > 0 ? $name : null,
            'email' => strlen($email) > 0 ? $email : null,
            'age' => 0,
            'google_id' => $googleId,
        ]);
    }

    private function buildUniqueLogin(string $email, string $googleId): string
    {
        $candidate = mb_strtolower($email);

        if (strlen($candidate) > 0) {
            $candidate = explode('@', $candidate)[0];
        }

        $candidate = preg_replace('/[^a-z0-9_]/', '', $candidate);

        if (!$candidate || strlen($candidate) < 3) {
            $candidate = 'google_' . substr($googleId, 0, 8);
        }

        $candidate = substr($candidate, 0, 20);

        $login = $candidate;
        $index = 1;

        while ($this->userRepository->getByLogin($login) !== null) {
            $suffix = strval($index);
            $baseLen = max(1, 20 - strlen($suffix));

            $login = substr($candidate, 0, $baseLen) . $suffix;
            $index++;
        }

        return $login;
    }

    /**
     * @return array<string,mixed>
     */
    private function verifyIdToken(string $idToken): array
    {
        if (strlen($idToken) === 0) {
            throw new Exception('Google id_token is required.');
        }

        $response = $this->httpClient->request(
            'GET',
            'https://oauth2.googleapis.com/tokeninfo',
            [
                'query' => [
                    'id_token' => $idToken,
                ],
                'http_errors' => false,
                'timeout' => 5,
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Google token verification failed.');
        }

        $payload = json_decode(strval($response->getBody()), true);

        if (!is_array($payload)) {
            throw new Exception('Google token verification failed.');
        }

        $aud = strval($payload['aud'] ?? '');

        if (!$this->isAllowedAudience($aud)) {
            throw new Exception('Google token audience is not allowed.');
        }

        $emailVerified = strval($payload['email_verified'] ?? 'false');

        if ($emailVerified !== 'true') {
            throw new Exception('Google account e-mail is not verified.');
        }

        return $payload;
    }

    private function isAllowedAudience(string $aud): bool
    {
        if (strlen($aud) === 0) {
            return false;
        }

        $clientIds = $this->settingsProvider->get('google_auth.client_ids') ?? '';

        if (is_array($clientIds)) {
            $allowed = $clientIds;
        } else {
            $allowed = array_filter(
                array_map('trim', explode(',', strval($clientIds))),
                fn (string $id) => strlen($id) > 0
            );
        }

        if (count($allowed) === 0) {
            return false;
        }

        return in_array($aud, $allowed);
    }
}
