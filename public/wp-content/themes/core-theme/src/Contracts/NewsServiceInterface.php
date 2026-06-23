<?php

declare(strict_types=1);

namespace CoreTheme\Contracts;

/**
 * Contrato de serviço de notícias.
 *
 * Camada intermediária entre o bloco e o repositório.
 * Cada estratégia pode ter sua própria lógica de orquestração aqui
 * (filtros, ordenação, transformação de dados, cache, etc.).
 */
interface NewsServiceInterface
{
    /**
     * Retorna as notícias prontas para renderização.
     *
     * @return object[]
     */
    public function getNews(array $attributes = []): array;

    /** Retorna o total de notícias disponíveis. */
    public function getNewsCount(array $attributes = []): int;
}
