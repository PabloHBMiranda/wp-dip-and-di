<?php

declare(strict_types=1);

namespace CoreTheme\Implementations\QueryBuilder;

use CoreTheme\Contracts\NewsQueryBuilderInterface;

final class DefaultNewsQueryBuilder implements NewsQueryBuilderInterface
{
    public function build(array $attributes): array
    {
        return [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $attributes['postsPerPage'] ?? 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
    }
}
