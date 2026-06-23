<?php

declare(strict_types=1);

namespace G1News\Implementations\Service;

use CoreTheme\Contracts\NewsServiceInterface;
use G1News\Implementations\Repository\G1NewsRepository;

final class G1NewsService implements NewsServiceInterface
{
    public function __construct(
        private readonly G1NewsRepository $repository,
    ) {}

    public function getNews(array $attributes = []): array
    {
        return $this->repository->findAll($attributes);
    }

    public function getNewsCount(array $attributes = []): int
    {
        return $this->repository->count($attributes);
    }
}
