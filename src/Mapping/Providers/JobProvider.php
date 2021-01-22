<?php

namespace App\Mapping\Providers;

use App\Jobs\LoadUncheckedDictWordsJob;
use App\Jobs\MatchDanglingDictWordsJob;
use App\Jobs\UpdateAssociationsJob;
use App\Jobs\UpdateWordsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class JobProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            LoadUncheckedDictWordsJob::class =>
                fn (ContainerInterface $c) => new LoadUncheckedDictWordsJob(
                    $c->get(WordRepositoryInterface::class),
                    $c->get(DictionaryService::class),
                    $c->get(SettingsProviderInterface::class)
                ),

            MatchDanglingDictWordsJob::class =>
                fn (ContainerInterface $c) => new MatchDanglingDictWordsJob(
                    $c->get(DictWordRepositoryInterface::class),
                    $c->get(WordRepositoryInterface::class),
                    $c->get(DictionaryService::class),
                    $c->get(SettingsProviderInterface::class)
                ),

            UpdateAssociationsJob::class =>
                fn (ContainerInterface $c) => new UpdateAssociationsJob(
                    $c->get(AssociationRepositoryInterface::class),
                    $c->get(SettingsProviderInterface::class),
                    $c->get(EventDispatcher::class)
                ),

            UpdateWordsJob::class =>
                fn (ContainerInterface $c) => new UpdateWordsJob(
                    $c->get(WordRepositoryInterface::class),
                    $c->get(SettingsProviderInterface::class),
                    $c->get(EventDispatcher::class)
                ),
        ];
    }
}
