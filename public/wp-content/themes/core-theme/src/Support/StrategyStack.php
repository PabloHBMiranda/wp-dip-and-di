<?php

declare(strict_types=1);

namespace CoreTheme\Support;

/**
 * Value object que mapeia contratos às implementações concretas de uma estratégia.
 *
 * Um plugin cria uma StrategyStack, vincula suas implementações via bind() e a
 * registra no NewsBlockRegistry. Em tempo de renderização, o registry usa o stack
 * para descobrir qual classe concreta instanciar para cada contrato solicitado.
 *
 * Exemplo de uso em um provider:
 *
 *   $stack = new StrategyStack('g1', 'G1 - Globo');
 *   $stack->bind(NewsRepositoryInterface::class, G1NewsRepository::class)
 *         ->bind(NewsServiceInterface::class,    G1NewsService::class)
 *         ->bind(NewsRendererInterface::class,   G1NewsRenderer::class);
 *   $registry->register($stack);
 */
final class StrategyStack
{
    /**
     * Mapa de contrato → implementação concreta.
     *
     * @var array<class-string, class-string>
     */
    private array $bindings = [];

    public function __construct(
        /** Identificador único da estratégia (ex.: "g1", "bbc", "default"). */
        private readonly string $alias,
        /** Rótulo legível exibido no SelectControl do editor de blocos. */
        private readonly string $label,
    ) {}

    /**
     * Vincula um contrato a uma implementação concreta nesta estratégia.
     * Retorna $this para permitir encadeamento fluente.
     *
     * @param class-string $contract       Interface/contrato a ser satisfeito.
     * @param class-string $implementation Classe concreta que o implementa.
     */
    public function bind(string $contract, string $implementation): self
    {
        $this->bindings[$contract] = $implementation;
        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /** @return array<class-string, class-string> */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Retorna a classe concreta vinculada ao contrato, ou null se não houver binding.
     * O registry usa o null para acionar o fallback para a estratégia "default".
     *
     * @param class-string $contract
     */
    public function getImplementation(string $contract): ?string
    {
        return $this->bindings[$contract] ?? null;
    }

    public function hasBinding(string $contract): bool
    {
        return isset($this->bindings[$contract]);
    }
}
