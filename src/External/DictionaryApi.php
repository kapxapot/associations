<?php

namespace App\External;

use App\External\Interfaces\DefinitionSourceInterface;
use App\Models\DTO\DefinitionData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Wrapper for api.dictionaryapi.dev - unofficial API for Google Dictionary.
 */
class DictionaryApi implements DefinitionSourceInterface
{
    use LoggerAwareTrait;

    private const SOURCE = 'dictionaryapi.dev';

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function request(string $languageCode, string $word): ?DefinitionData
    {
        $url = $this->buildUrl($languageCode, $word);

        /** @var ResponseInterface $response */
        $response = null;

        $client = new Client();

        try {
            $response = $client->get($url);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        } catch (GuzzleException $ex) {
            $this->logger->info(
                'Failed to load from dictionary api url ' . $url
                . ' with exception: ' . $ex->getMessage()
            );

            return null;
        }

        return new DefinitionData(
            self::SOURCE,
            $url,
            $response->getBody()
        );
    }

    private function buildUrl(string $languageCode, string $word): string
    {
        return sprintf(
            'https://api.dictionaryapi.dev/api/v2/entries/%s/%s',
            $languageCode,
            urlencode($word)
        );
    }
}
