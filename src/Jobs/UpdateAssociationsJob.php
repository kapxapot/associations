<?php

namespace App\Jobs;

use App\Events\AssociationOutOfDateEvent;
use App\Models\Association;
use Plasticode\Collection;
use Plasticode\Contained;

class UpdateAssociationsJob extends Contained
{
    public function run() : Collection
    {
        $limit = $this->getSettings('associations.update.limit');
        $ttl = $this->getSettings('associations.update.ttl_min');

        $outOfDate = Association::getOutOfDate($ttl)
            ->limit($limit)
            ->all();

        foreach ($outOfDate as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->dispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
