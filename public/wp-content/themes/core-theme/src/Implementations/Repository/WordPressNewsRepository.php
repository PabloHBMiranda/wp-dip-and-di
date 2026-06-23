<?php

declare(strict_types=1);

namespace CoreTheme\Implementations\Repository;

use CoreTheme\Contracts\NewsQueryBuilderInterface;
use CoreTheme\Contracts\NewsRepositoryInterface;

/**
 * Repositório padrão — busca notícias no banco de dados do WordPress via WP_Query.
 *
 * Recebe o NewsQueryBuilderInterface por injeção de dependência: o container
 * injeta o DefaultNewsQueryBuilder automaticamente via autowiring.
 */
final class WordPressNewsRepository implements NewsRepositoryInterface
{
    public function __construct(
        private readonly NewsQueryBuilderInterface $queryBuilder,
    ) {}

    public function findAll(array $args = []): array
    {
        // O queryBuilder transforma os atributos do bloco em args do WP_Query.
        $query = new \WP_Query($this->queryBuilder->build($args));
        return $query->posts ?? [];
    }

    public function findById(int $id): ?object
    {
        $post = get_post($id);
        return ($post instanceof \WP_Post) ? $post : null;
    }

    public function count(array $args = []): int
    {
        $wpArgs           = $this->queryBuilder->build($args);
        $wpArgs['fields'] = 'ids'; // só conta, não carrega posts completos
        $query            = new \WP_Query($wpArgs);
        return (int) $query->found_posts;
    }
}
