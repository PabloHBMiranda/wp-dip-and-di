<?php

declare(strict_types=1);

namespace CoreTheme\Contracts;

/**
 * Contrato de renderização de notícias.
 *
 * Cada estratégia implementa este contrato com sua própria estrutura HTML.
 * O bloco não conhece nenhuma implementação concreta — apenas chama render()
 * e devolve o resultado para o WordPress.
 */
interface NewsRendererInterface
{
    /**
     * Gera e retorna o HTML da lista de notícias.
     *
     * @param object[] $news      Lista de notícias retornada pelo serviço.
     * @param array    $attributes Atributos do bloco (strategy, postsPerPage…).
     */
    public function render(array $news, array $attributes): string;
}
