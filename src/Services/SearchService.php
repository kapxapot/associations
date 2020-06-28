<?php

namespace App\Services;

use App\Models\News;
use App\Models\Page;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Collections\Basic\ArrayCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Tag;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class SearchService
{
    private NewsRepositoryInterface $newsRepository;
    private PageRepositoryInterface $pageRepository;
    private TagRepositoryInterface $tagRepository;

    private LinkerInterface $linker;

    public function __construct(
        NewsRepositoryInterface $newsRepository,
        PageRepositoryInterface $pageRepository,
        TagRepositoryInterface $tagRepository,
        LinkerInterface $linker
    )
    {
        $this->newsRepository = $newsRepository;
        $this->pageRepository = $pageRepository;
        $this->tagRepository = $tagRepository;

        $this->linker = $linker;
    }

    public function search($query) : ArrayCollection
    {
        $news = $this
            ->newsRepository
            ->search($query)
            ->map(
                fn (News $n) =>
                [
                    'type' => 'news',
                    'data' => $n->serialize(),
                    'text' => $n->displayTitle(),
                    'code' => $n->code(),
                    'url' => $this->linker->abs($n->url()),
                ]
            );

        $pages = $this
            ->pageRepository
            ->search($query)
            ->map(
                fn (Page $a) =>
                [
                    'type' => 'page',
                    'data' => $a->serialize(),
                    'text' => $a->title,
                    'code' => $a->code(),
                    'url' => $this->linker->abs($a->url()),
                ]
            );


        $tags = $this
            ->tagRepository
            ->search($query)
            ->distinctBy('tag')
            ->map(
                fn (Tag $t) =>
                [
                    'type' => 'tag',
                    'text' => $t->tag,
                    'code' => $t->code(),
                    'url' => $this->linker->abs($t->url()),
                ]
            );

        return ArrayCollection::merge(
            ArrayCollection::from($news),
            ArrayCollection::from($pages),
            ArrayCollection::from($tags)
        );
    }
}
