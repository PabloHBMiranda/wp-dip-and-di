<?php

declare(strict_types=1);

namespace CoreTheme\Bootstrap;

use CoreTheme\Contracts\ServiceProviderInterface;
use CoreTheme\Registry\NewsBlockRegistry;
use Illuminate\Container\Container;

/**
 * Ponto de entrada da aplicação — singleton que possui o container de DI e o registry.
 *
 * Ciclo de vida:
 *   1. O tema instancia a Application e registra seu próprio provider (estratégia "default").
 *   2. O hook `core_theme_register_providers` dispara, permitindo que plugins e child themes
 *      chamem registerProvider() com seus próprios providers.
 *   3. boot() é chamado uma única vez após todos os providers estarem registrados;
 *      ele executa o boot() de cada provider para popular o registry.
 *   4. Chamadas a registerProvider() após o boot() executam o boot() imediatamente,
 *      garantindo que plugins ativados tarde ainda funcionem.
 */
final class Application
{
    private static ?self $instance = null;

    private readonly Container $container;
    private readonly NewsBlockRegistry $registry;

    /** @var ServiceProviderInterface[] */
    private array $providers = [];

    /** Indica se o boot() já foi executado. */
    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();
        $this->registry  = new NewsBlockRegistry($this->container);

        // Torna o próprio container e o registry resolúveis pelo container.
        // Permite que qualquer classe receba-os via injeção de dependência.
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(NewsBlockRegistry::class, $this->registry);
    }

    /** Retorna a instância única da aplicação (padrão Singleton). */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registra um provider e, se o boot já ocorreu, executa seu boot imediatamente.
     * Isso garante que plugins ativados depois do tema ainda consigam se registrar.
     */
    public function registerProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this->container);
        $this->providers[] = $provider;

        if ($this->booted) {
            $provider->boot($this->registry);
        }
    }

    /**
     * Executa o boot() de todos os providers registrados.
     * Idempotente — chamadas subsequentes são ignoradas.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot($this->registry);
        }

        $this->booted = true;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRegistry(): NewsBlockRegistry
    {
        return $this->registry;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}
