<?php

namespace App\Services;

use App\Repositories\Interfaces\NewsRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;

class TagPartsProviderService
{
    private NewsRepositoryInterface $newsRepository;
    private PageRepositoryInterface $pageRepository;

    public function __construct(
        NewsRepositoryInterface $newsRepository,
        PageRepositoryInterface $pageRepository
    )
    {
        $this->newsRepository = $newsRepository;
        $this->pageRepository = $pageRepository;
    }

    public function getParts(string $tag) : array
    {
        $groups = [
            [
                'id' => 'news',
                'label' => 'Новости',
                'values' => $this->newsRepository->getAllByTag($tag),
                'component' => 'news',
            ],
            [
                'id' => 'pages',
                'label' => 'Страницы',
                'values' => $this->pageRepository->getAllByTag($tag),
                'component' => 'pages',
            ],
        ];

        return array_filter(
            $groups,
            fn (array $a) => count($a['values']) > 0
        );
    }
}
