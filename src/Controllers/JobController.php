<?php

namespace App\Controllers;

use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;

class JobController extends Controller
{
    public function updateAssociations($request, $response, $args)
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

    public function updateWords($request, $response, $args)
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
