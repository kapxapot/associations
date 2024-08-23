<?php

namespace Brightwood\Translation;

use Brightwood\JsonDataLoader;
use Brightwood\Translation\Interfaces\TranslatorFactoryInterface;
use Brightwood\Translation\Interfaces\TranslatorInterface;
use Exception;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class TranslatorFactory implements TranslatorFactoryInterface
{
    use LoggerAwareTrait;

    public function __construct(LoggerInterface $logger)
    {
        $this->withLogger($logger);
    }

    public function __invoke(string $langCode): TranslatorInterface
    {
        $dictionary = $this->getDictionary($langCode);
        return new Translator($dictionary);
    }

    /**
     * @throws Exception
     */
    private function getDictionary(string $langCode): ?array
    {
        $path = __DIR__ . "/Locales/{$langCode}.json";

        try {
            return JsonDataLoader::load($path);
        } catch (Exception $ex) {
            $this->logEx($ex);
        }

        return null;
    }
}
