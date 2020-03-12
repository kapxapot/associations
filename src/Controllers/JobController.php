<?php

namespace App\Controllers;

use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property SettingsProviderInterface $settingsProvider
 * @property EventDispatcher $dispatcher
 */
class JobController extends Controller
{
    public function updateAssociations(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $start = microtime(true);

        $job = new UpdateAssociationsJob(
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
