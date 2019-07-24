<?php

namespace App\Controllers;

use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends Controller
{
    public function updateAssociations(ServerRequestInterface $request, ResponseInterface $response)
    {
        $start = microtime(true);
        $job = new UpdateAssociationsJob($this->container);
        $end = microtime(true);

        var_dump([
            'time' => $end - $start,
            'ids' => $job->run()->ids(),
        ]);

        return;
    }

    public function updateWords(ServerRequestInterface $request, ResponseInterface $response)
    {
        $start = microtime(true);
        $job = new UpdateWordsJob($this->container);
        $end = microtime(true);

        var_dump([
            'time' => $end - $start,
            'ids' => $job->run()->ids(),
        ]);
        
        return;
    }
}
