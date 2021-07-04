<?php

namespace App\Search;

use Plasticode\Util\SortStep;
use Psr\Http\Message\ServerRequestInterface;

class SearchParams
{
    private ?int $offset;
    private ?int $limit;
    private ?string $filter;

    /**
     * @var SortStep[]|null
     */
    private ?array $sort;

    public function __construct(
        ?int $offset = null,
        ?int $limit = null,
        ?string $filter = null,
        ?array $sort = null
    )
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->filter = $filter;
        $this->sort = $sort;
    }

    public function hasOffset(): bool
    {
        return $this->offset !== null;
    }

    public function offset(): ?int
    {
        return $this->offset;
    }

    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function hasFilter(): bool
    {
        return strlen($this->filter) > 0;
    }

    public function filter(): ?string
    {
        return $this->filter;
    }

    public function hasSort(): bool
    {
        return !empty($this->sort);
    }

    /**
     * @return SortStep[]|null
     */
    public function sort(): ?array
    {
        return $this->sort;
    }

    public static function fromRequest(ServerRequestInterface $request): self
    {
        $queryParams = $request->getQueryParams();

        $offset = $queryParams['start'] ?? null;
        $limit = $queryParams['length'] ?? null;
        $filter = $queryParams['search']['value'] ?? null;

        $sort = [];

        $columns = $queryParams['columns'] ?? [];
        $order = $queryParams['order'] ?? [];

        foreach ($order as $orderItem) {
            $columnIndex = $orderItem['column'];
            $dir = $orderItem['dir'];

            $column = $columns[$columnIndex];
            $columnName = $column['name'];

            $sort[] = $dir === 'asc'
                ? SortStep::asc($columnName)
                : SortStep::desc($columnName);
        }

        return new self($offset, $limit, $filter, $sort);
    }
}
