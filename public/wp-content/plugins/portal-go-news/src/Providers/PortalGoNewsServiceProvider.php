<?php

declare(strict_types=1);

namespace PortalGo\Providers;

use CoreTheme\Contracts\NewsRendererInterface;
use CoreTheme\Contracts\NewsRepositoryInterface;
use CoreTheme\Contracts\NewsServiceInterface;
use CoreTheme\Contracts\ServiceProviderInterface;
use CoreTheme\Registry\NewsBlockRegistry;
use CoreTheme\Support\StrategyStack;
use Illuminate\Container\Container;
use PortalGo\Implementations\Renderer\PortalGoNewsRenderer;
use PortalGo\Implementations\Repository\PortalGoNewsRepository;
use PortalGo\Implementations\Service\PortalGoNewsService;

final class PortalGoNewsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void {}

    public function boot(NewsBlockRegistry $registry): void
    {
        $strategy = new StrategyStack('portal-go', 'Portal GO');

        $strategy
            ->bind(NewsRepositoryInterface::class, PortalGoNewsRepository::class)
            ->bind(NewsServiceInterface::class, PortalGoNewsService::class)
            ->bind(NewsRendererInterface::class, PortalGoNewsRenderer::class);

        $registry->register($strategy);
    }
}
