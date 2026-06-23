<?php

declare(strict_types=1);

namespace PortalGo\Implementations\Service;

use CoreTheme\Contracts\NewsServiceInterface;
use PortalGo\Implementations\Repository\PortalGoNewsRepository;

final class PortalGoNewsService implements NewsServiceInterface
{
    public function __construct(
        private readonly PortalGoNewsRepository $repository,
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
