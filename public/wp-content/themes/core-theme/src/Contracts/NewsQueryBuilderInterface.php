<?php

declare(strict_types=1);

namespace CoreTheme\Contracts;

interface NewsQueryBuilderInterface
{
    /**
     * Builds WP_Query args from block attributes.
     */
    public function build(array $attributes): array;
}
