<?php

namespace App\Tests\Mapping;

use App\Jobs\LoadUncheckedDictWordsJob;
use App\Jobs\MatchDanglingDictWordsJob;
use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Testing\AbstractProviderTest;

final class JobProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            SettingsProviderInterface::class,

            AssociationRepositoryInterface::class,
            DictWordRepositoryInterface::class,
            ExternalDictServiceInterface::class,
            WordRepositoryInterface::class,
        ];
    }

    public function testWiring(): void
    {
        $this->check(LoadUncheckedDictWordsJob::class);
        $this->check(MatchDanglingDictWordsJob::class);
        $this->check(UpdateAssociationsJob::class);
        $this->check(UpdateWordsJob::class);
    }
}
