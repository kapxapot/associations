<?php

namespace App\Controllers;

use App\Jobs\Interfaces\ModelJobInterface;
use App\Jobs\LoadUncheckedDictWordsJob;
use App\Jobs\MatchDanglingDictWordsJob;
use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    private LoadUncheckedDictWordsJob $loadUncheckedDictWordsJob;
    private MatchDanglingDictWordsJob $matchDanglingDictWordsJob;
    private UpdateAssociationsJob $updateAssociationsJob;
    private UpdateWordsJob $updateWordsJob;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->loadUncheckedDictWordsJob =
            $container->get(LoadUncheckedDictWordsJob::class);

        $this->matchDanglingDictWordsJob =
            $container->get(MatchDanglingDictWordsJob::class);

        $this->updateAssociationsJob =
            $container->get(UpdateAssociationsJob::class);

        $this->updateWordsJob =
            $container->get(UpdateWordsJob::class);
    }

    public function updateAssociations(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->updateAssociationsJob,
            'Updated associations'
        );
    }

    public function updateWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->updateWordsJob,
            'Updated words'
        );
    }

    public function loadUncheckedDictWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->loadUncheckedDictWordsJob,
            'Loaded unchecked dictionary words'
        );
    }

    public function matchDanglingDictWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->matchDanglingDictWordsJob,
            'Matched dangling dictionary words'
        );
    }

    private function runJob(
        ModelJobInterface $job,
        string $msg
    )
    {
        $start = microtime(true);

        $result = $job->run();

        $end = microtime(true);

        $msg .= ': ' . $result->count();

        $this->logger->info(
            $msg,
            [
                'time' => $end - $start,
                'ids' => $result->ids()->toArray(),
            ]
        );

        return $msg;
    }
}
