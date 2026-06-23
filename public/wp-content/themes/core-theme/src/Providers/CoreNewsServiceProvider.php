<?php

declare(strict_types=1);

namespace CoreTheme\Providers;

use CoreTheme\Contracts\NewsQueryBuilderInterface;
use CoreTheme\Contracts\NewsRendererInterface;
use CoreTheme\Contracts\NewsRepositoryInterface;
use CoreTheme\Contracts\NewsServiceInterface;
use CoreTheme\Contracts\ServiceProviderInterface;
use CoreTheme\Implementations\QueryBuilder\DefaultNewsQueryBuilder;
use CoreTheme\Implementations\Renderer\DefaultNewsRenderer;
use CoreTheme\Implementations\Repository\WordPressNewsRepository;
use CoreTheme\Implementations\Service\DefaultNewsService;
use CoreTheme\Registry\NewsBlockRegistry;
use CoreTheme\Support\StrategyStack;
use Illuminate\Container\Container;

/**
 * Provider principal do tema — registra a estratégia "default".
 *
 * A estratégia "default" busca posts diretamente do banco do WordPress
 * via WP_Query e serve como fallback quando o alias solicitado não existir
 * ou quando um plugin não sobrescrever todos os contratos.
 */
final class CoreNewsServiceProvider implements ServiceProviderInterface
{
    /**
     * Vincula os contratos às implementações padrão no container.
     * Esses bindings são o fallback global do autowiring para classes
     * que dependem dos contratos sem binding explícito de estratégia.
     */
    public function register(Container $container): void
    {
        $container->bind(NewsQueryBuilderInterface::class, DefaultNewsQueryBuilder::class);
        $container->bind(NewsRepositoryInterface::class, WordPressNewsRepository::class);
        $container->bind(NewsServiceInterface::class, DefaultNewsService::class);
        $container->bind(NewsRendererInterface::class, DefaultNewsRenderer::class);
    }

    /** Registra a StrategyStack "default" no registry. */
    public function boot(NewsBlockRegistry $registry): void
    {
        $strategy = new StrategyStack('default', __('Default', 'core-theme'));

        $strategy
            ->bind(NewsQueryBuilderInterface::class, DefaultNewsQueryBuilder::class)
            ->bind(NewsRepositoryInterface::class, WordPressNewsRepository::class)
            ->bind(NewsServiceInterface::class, DefaultNewsService::class)
            ->bind(NewsRendererInterface::class, DefaultNewsRenderer::class);

        $registry->register($strategy);
    }
}
