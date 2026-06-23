<?php

declare(strict_types=1);

namespace BbcNews\Providers;

use BbcNews\Implementations\Renderer\BbcNewsRenderer;
use BbcNews\Implementations\Repository\BbcNewsRepository;
use BbcNews\Implementations\Service\BbcNewsService;
use CoreTheme\Contracts\NewsRendererInterface;
use CoreTheme\Contracts\NewsRepositoryInterface;
use CoreTheme\Contracts\NewsServiceInterface;
use CoreTheme\Contracts\ServiceProviderInterface;
use CoreTheme\Registry\NewsBlockRegistry;
use CoreTheme\Support\StrategyStack;
use Illuminate\Container\Container;

final class BbcNewsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void {}

    public function boot(NewsBlockRegistry $registry): void
    {
        $strategy = new StrategyStack('bbc', 'BBC News');

        $strategy
            ->bind(NewsRepositoryInterface::class, BbcNewsRepository::class)
            ->bind(NewsServiceInterface::class, BbcNewsService::class)
            ->bind(NewsRendererInterface::class, BbcNewsRenderer::class);

        $registry->register($strategy);
    }
}
