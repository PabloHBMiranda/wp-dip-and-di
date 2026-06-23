<?php

declare(strict_types=1);

namespace CoreTheme\Contracts;

/**
 * Contrato de acesso a dados de notícias.
 *
 * Quem implementar esta interface decide DE ONDE os dados vêm:
 * banco de dados do WordPress, feed RSS, REST API externa, etc.
 * O restante do sistema não sabe — e não precisa saber.
 */
interface NewsRepositoryInterface
{
    /**
     * Retorna uma lista de notícias de acordo com os atributos do bloco
     * (ex.: postsPerPage, strategy…).
     *
     * @return object[]
     */
    public function findAll(array $args = []): array;

    /** Retorna uma notícia pelo ID, ou null se não encontrada. */
    public function findById(int $id): ?object;

    /** Conta o total de notícias disponíveis para os argumentos fornecidos. */
    public function count(array $args = []): int;
}
