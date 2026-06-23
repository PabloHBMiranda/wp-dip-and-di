<?php

declare(strict_types=1);

namespace CoreTheme\Registry;

use CoreTheme\Support\StrategyStack;
use Illuminate\Container\Container;
use RuntimeException;

/**
 * Registry central que mapeia aliases de estratégia às suas StrategyStacks.
 *
 * É o coração do padrão Strategy + DI desta POC:
 * - Armazena todas as estratégias registradas pelos plugins/providers.
 * - Resolve a implementação correta para cada contrato em tempo de renderização.
 * - Aplica fallback para a estratégia "default" quando necessário (alias desconhecido
 *   ou contrato não sobrescrito pela estratégia solicitada).
 * - Usa o container do Laravel para instanciar as classes concretas, garantindo
 *   que as dependências delas também sejam injetadas automaticamente.
 */
final class NewsBlockRegistry
{
    /**
     * Mapa de alias → StrategyStack.
     *
     * @var array<string, StrategyStack>
     */
    private array $strategies = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /** Registra uma estratégia no registry pelo seu alias. */
    public function register(StrategyStack $strategy): void
    {
        $this->strategies[$strategy->getAlias()] = $strategy;
    }

    /**
     * Resolve a implementação concreta de um contrato para o alias solicitado.
     *
     * Lógica de fallback (nesta ordem):
     *   1. Usa a StrategyStack do alias solicitado, se ela existir.
     *   2. Se o alias não existir, cai para a estratégia "default".
     *   3. Se a estratégia encontrada não tiver binding para o contrato,
     *      tenta o binding da "default".
     *
     * O container instancia a classe concreta, injetando suas dependências.
     *
     * @template T of object
     * @param  class-string<T> $contract
     * @return T
     */
    public function resolve(string $alias, string $contract): object
    {
        $strategy = $this->strategies[$alias] ?? $this->strategies['default'] ?? null;

        if ($strategy === null) {
            throw new RuntimeException(
                "Nenhuma estratégia registrada para o alias '{$alias}' e nenhum fallback 'default' existe."
            );
        }

        // Se a estratégia não tiver binding para este contrato, usa o da "default".
        $implementation = $strategy->getImplementation($contract)
            ?? $this->strategies['default']?->getImplementation($contract);

        if ($implementation === null) {
            throw new RuntimeException(
                "Nenhuma implementação encontrada para [{$contract}] na estratégia '{$alias}'."
            );
        }

        // O container resolve a classe concreta com autowiring completo.
        /** @var T */
        return $this->container->make($implementation);
    }

    public function hasStrategy(string $alias): bool
    {
        return isset($this->strategies[$alias]);
    }

    /**
     * Retorna a lista de estratégias para o SelectControl no editor de blocos.
     * Exposta via endpoint REST em /core-theme/v1/news-block/strategies.
     *
     * @return array<array{value: string, label: string}>
     */
    public function getAvailableStrategies(): array
    {
        return array_values(
            array_map(
                fn(StrategyStack $s) => ['value' => $s->getAlias(), 'label' => $s->getLabel()],
                $this->strategies,
            )
        );
    }

    /** @return array<string, StrategyStack> */
    public function all(): array
    {
        return $this->strategies;
    }
}
