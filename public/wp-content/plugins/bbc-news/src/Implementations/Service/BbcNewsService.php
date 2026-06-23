<?php

declare(strict_types=1);

namespace BbcNews\Implementations\Service;

use BbcNews\Implementations\Repository\BbcNewsRepository;
use CoreTheme\Contracts\NewsServiceInterface;

final class BbcNewsService implements NewsServiceInterface
{
    public function __construct(
        private readonly BbcNewsRepository $repository,
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
