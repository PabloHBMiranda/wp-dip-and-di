<?php

declare(strict_types=1);

namespace G1News\Providers;

use CoreTheme\Contracts\NewsRendererInterface;
use CoreTheme\Contracts\NewsRepositoryInterface;
use CoreTheme\Contracts\NewsServiceInterface;
use CoreTheme\Contracts\ServiceProviderInterface;
use CoreTheme\Registry\NewsBlockRegistry;
use CoreTheme\Support\StrategyStack;
use G1News\Implementations\Renderer\G1NewsRenderer;
use G1News\Implementations\Repository\G1NewsRepository;
use G1News\Implementations\Service\G1NewsService;
use Illuminate\Container\Container;

final class G1NewsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void {}

    public function boot(NewsBlockRegistry $registry): void
    {
        $strategy = new StrategyStack('g1', 'G1 - Globo');

        $strategy
            ->bind(NewsRepositoryInterface::class, G1NewsRepository::class)
            ->bind(NewsServiceInterface::class, G1NewsService::class)
            ->bind(NewsRendererInterface::class, G1NewsRenderer::class);

        $registry->register($strategy);
    }
}
