<?php

namespace App\Controllers;

use App\Factories\UpdateAssociationsJobFactory;
use App\Factories\UpdateDictWordsJobFactory;
use App\Factories\UpdateWordsJobFactory;
use Plasticode\Collections\Basic\DbModelCollection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    private UpdateAssociationsJobFactory $updateAssociationsJobFactory;
    private UpdateDictWordsJobFactory $updateDictWordsJobFactory;
    private UpdateWordsJobFactory $updateWordsJobFactory;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->updateAssociationsJobFactory = $container->updateAssociationsJobFactory;
        $this->updateDictWordsJobFactory = $container->updateDictWordsJobFactory;
        $this->updateWordsJobFactory = $container->updateWordsJobFactory;
    }

    public function updateAssociations(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $start = microtime(true);

        $job = $this->updateAssociationsJobFactory->make();
        $result = $job->run();

        $end = microtime(true);

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

        $job = $this->updateWordsJobFactory->make();
        $result = $job->run();

        $end = microtime(true);

        $msg = 'Updated words: ' . $result->count();

        $this->logCollectionResult($result, $msg, $start, $end);

        return $msg;
    }

    public function updateDictWords(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $start = microtime(true);

        $job = $this->updateDictWordsJobFactory->make();
        $result = $job->run();

        $end = microtime(true);

        $result = $job->run();
        $msg = 'Updated dictionary words: ' . $result->count();

        $this->logCollectionResult($result, $msg, $start, $end);

        return $msg;
    }

    private function logCollectionResult(
        DbModelCollection $result,
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
