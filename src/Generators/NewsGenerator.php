<?php

namespace App\Generators;

use App\Models\News;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class NewsGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private NewsRepositoryInterface $newsRepository;

    public function __construct(
        GeneratorContext $context,
        NewsRepositoryInterface $newsRepository,
        TagRepositoryInterface $tagRepository
    )
    {
        parent::__construct($context, $tagRepository);

        $this->newsRepository = $newsRepository;
    }

    protected function entityClass(): string
    {
        return News::class;
    }

    protected function getRepository(): NewsRepositoryInterface
    {
        return $this->newsRepository;
    }

    public function beforeSave(array $data, $id = null): array
    {
        $data = $this->publishableBeforeSave($data, $id);

        return $data;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $news = $this->newsRepository->get($id);

        if ($news) {
            $item['url'] = $news->url();
        }

        return $item;
    }
}
