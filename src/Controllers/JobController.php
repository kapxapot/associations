<?php

namespace App\Controllers;

use App\Factories\Interfaces\ModelJobFactoryInterface;
use App\Factories\LoadUncheckedDictWordsJobFactory;
use App\Factories\MatchDanglingDictWordsJobFactory;
use App\Factories\UpdateAssociationsJobFactory;
use App\Factories\UpdateWordsJobFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    private LoadUncheckedDictWordsJobFactory $loadUncheckedDictWordsJobFactory;
    private MatchDanglingDictWordsJobFactory $matchDanglingDictWordsJobFactory;
    private UpdateAssociationsJobFactory $updateAssociationsJobFactory;
    private UpdateWordsJobFactory $updateWordsJobFactory;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->loadUncheckedDictWordsJobFactory = $container->loadUncheckedDictWordsJobFactory;
        $this->matchDanglingDictWordsJobFactory = $container->matchDanglingDictWordsJobFactory;
        $this->updateAssociationsJobFactory = $container->updateAssociationsJobFactory;
        $this->updateWordsJobFactory = $container->updateWordsJobFactory;
    }

    public function updateAssociations(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->updateAssociationsJobFactory,
            'Updated associations'
        );
    }

    public function updateWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->updateWordsJobFactory,
            'Updated words'
        );
    }

    public function loadUncheckedDictWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->loadUncheckedDictWordsJobFactory,
            'Loaded unchecked dictionary words'
        );
    }

    public function matchDanglingDictWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->runJob(
            $this->matchDanglingDictWordsJobFactory,
            'Matched dangling dictionary words'
        );
    }

    private function runJob(
        ModelJobFactoryInterface $factory,
        string $msg
    )
    {
        $start = microtime(true);

        $job = $factory->make();
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
