<?php

namespace App\Controllers;

use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    private EventDispatcher $dispatcher;
    private SettingsProviderInterface $settingsProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->dispatcher = $container->dispatcher;
        $this->settingsProvider = $container->settingsProvider;
    }

    public function updateAssociations(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $start = microtime(true);

        $job = new UpdateAssociationsJob(
            $this->associationRepository,
            $this->settingsProvider,
            $this->dispatcher
        );

        $end = microtime(true);

        $result = $job->run();
        $msg = 'Updated associations: ' . $result->count();

        $this->logCollectionResult($result, $msg, $start, $end);

        return $msg;
    }

    public function updateWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $start = microtime(true);

        $job = new UpdateWordsJob(
            $this->wordRepository,
            $this->settingsProvider,
            $this->dispatcher
        );

        $end = microtime(true);

        $result = $job->run();
        $msg = 'Updated words: ' . $result->count();

        $this->logCollectionResult($result, $msg, $start, $end);

        return $msg;
    }

    private function logCollectionResult(
        Collection $result,
        string $msg,
        $start,
        $end
    ) : void
    {
        $this->logger->info(
            $msg,
            [
                'time' => $end - $start,
                'ids' => $result->ids()->toArray(),
            ]
        );
    }
}
