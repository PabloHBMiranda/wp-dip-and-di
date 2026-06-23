<?php

declare(strict_types=1);

namespace CoreTheme\Implementations\Service;

use CoreTheme\Contracts\NewsRepositoryInterface;
use CoreTheme\Contracts\NewsServiceInterface;

final class DefaultNewsService implements NewsServiceInterface
{
    public function __construct(
        private readonly NewsRepositoryInterface $repository,
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
