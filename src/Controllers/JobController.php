<?php

namespace App\Controllers;

use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    private AssociationRepositoryInterface $associationRepository;
    private WordRepositoryInterface $wordRepository;

    private SettingsProviderInterface $settingsProvider;
    private EventDispatcher $dispatcher;

    public function __construct(ContainerInterface $container)
    {
        $this->associationRepository = $container->associationRepository;
        $this->wordRepository = $container->wordRepository;

        $this->settingsProvider = $container->settingsProvider;
        $this->dispatcher = $container->dispatcher;
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

        $this->logger->info(
            'Updated associations.',
            [
                'time' => $end - $start,
                'ids' => $job->run()->ids(),
            ]
        );
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

        $this->logger->info(
            'Updated words.',
            [
                'time' => $end - $start,
                'ids' => $job->run()->ids(),
            ]
        );
    }
}
