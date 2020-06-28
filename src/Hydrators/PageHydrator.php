<?php

namespace App\Hydrators;

use App\Models\Page;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\NewsSourceHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

class PageHydrator extends NewsSourceHydrator
{
    private PageRepositoryInterface $pageRepository;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct(
            $userRepository,
            $cutParser,
            $linker,
            $parser
        );

        $this->pageRepository = $pageRepository;
    }

    /**
     * @param Page $entity
     */
    public function hydrate(DbModel $entity) : Page
    {
        /** @var Page */
        $entity = parent::hydrate($entity);

        return $entity
            ->withChildren(
                fn () => $this->pageRepository->getChildren($entity)
            )
            ->withParent(
                fn () => $this->pageRepository->get($entity->parentId)
            )
            ->withUrl(
                fn () => $this->linker->page($entity)
            );
    }
}
