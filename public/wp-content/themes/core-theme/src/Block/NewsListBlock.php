<?php

declare(strict_types=1);

namespace CoreTheme\Block;

use CoreTheme\Contracts\NewsRendererInterface;
use CoreTheme\Contracts\NewsServiceInterface;
use CoreTheme\Registry\NewsBlockRegistry;

/**
 * Registra o bloco "news-list" e trata a renderização server-side.
 *
 * Este bloco é intencionalmente "burro": ele não conhece nenhuma estratégia
 * concreta. Apenas lê o atributo "strategy" dos atributos do bloco, pede
 * ao registry as implementações corretas e delega o trabalho a elas.
 *
 * Este é o ponto central onde o DIP se manifesta na renderização:
 * o bloco depende apenas dos contratos NewsServiceInterface e
 * NewsRendererInterface — nunca de G1NewsService, BbcNewsRenderer, etc.
 */
final class NewsListBlock
{
    public function __construct(
        private readonly NewsBlockRegistry $registry,
    ) {}

    /** Registra o bloco no WordPress usando o block.json do diretório. */
    public function register(): void
    {
        register_block_type(
            get_template_directory() . '/blocks/news-list',
            ['render_callback' => $this->render(...)]
        );
    }

    /**
     * Callback de renderização chamado pelo WordPress a cada exibição do bloco.
     *
     * Fluxo:
     *   1. Lê o alias da estratégia dos atributos do bloco.
     *   2. Pede ao registry o serviço e o renderer correspondentes.
     *   3. Busca as notícias via serviço.
     *   4. Delega a geração do HTML ao renderer.
     *
     * Não existe if/else para escolher entre estratégias — o registry + container
     * fazem esse despacho de forma transparente.
     */
    public function render(array $attributes): string
    {
        $alias = $attributes['strategy'] ?? 'default';

        // O registry resolve a classe concreta certa sem nenhum condicional.
        $service  = $this->registry->resolve($alias, NewsServiceInterface::class);
        $renderer = $this->registry->resolve($alias, NewsRendererInterface::class);

        $news = $service->getNews($attributes);

        return $renderer->render($news, $attributes);
    }
}
