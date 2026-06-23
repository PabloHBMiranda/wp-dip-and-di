<?php

declare(strict_types=1);

namespace CoreTheme\Contracts;

use CoreTheme\Registry\NewsBlockRegistry;
use Illuminate\Container\Container;

/**
 * Contrato de Service Provider.
 *
 * Cada plugin/child theme cria um provider que implementa esta interface
 * para participar do sistema de DI sem tocar no código do tema principal.
 *
 * Ciclo de vida:
 *   1. register() — vincula concretas no container (sem acesso ao registry ainda).
 *   2. boot()     — registra a StrategyStack no registry (após todos os providers
 *                   terem rodado seu register()).
 */
interface ServiceProviderInterface
{
    /**
     * Liga interfaces às implementações concretas no container.
     * Chamado antes do boot() — não acesse o registry aqui.
     */
    public function register(Container $container): void;

    /**
     * Registra a estratégia no registry com seu alias e bindings.
     * Chamado após todos os providers terem sido registrados.
     */
    public function boot(NewsBlockRegistry $registry): void;
}
