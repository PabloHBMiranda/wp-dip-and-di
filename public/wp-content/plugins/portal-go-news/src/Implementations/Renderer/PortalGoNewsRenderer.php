<?php

declare(strict_types=1);

namespace PortalGo\Implementations\Renderer;

use CoreTheme\Contracts\NewsRendererInterface;

/**
 * Layout Portal GO: featured com texto sobreposto na imagem (overlay) +
 * lista de itens com thumbnail pequeno à direita e texto à esquerda.
 */
final class PortalGoNewsRenderer implements NewsRendererInterface
{
    public function render(array $news, array $attributes): string
    {
        if (empty($news)) {
            return '<p class="pgo-news__empty">Nenhuma notícia encontrada.</p>';
        }

        $featured = array_shift($news);
        $html     = '<div class="pgo-news">';

        // ── featured: imagem com overlay de texto ─────────────────────────────
        $featuredImage = $featured->image
            ? sprintf('<img src="%s" alt="" class="pgo-news__featured-img">', esc_url($featured->image))
            : '';

        $html .= sprintf(
            '<article class="pgo-news__featured">
                <a href="%s" target="_blank" rel="noopener noreferrer" class="pgo-news__featured-link">
                    <figure class="pgo-news__featured-figure">
                        %s
                        <figcaption class="pgo-news__featured-overlay">
                            <span class="pgo-news__tag">Portal GO</span>
                            <h2 class="pgo-news__featured-title">%s</h2>
                            <time class="pgo-news__featured-date">%s</time>
                        </figcaption>
                    </figure>
                </a>
            </article>',
            esc_url($featured->link),
            $featuredImage,
            esc_html($featured->title),
            esc_html($featured->date),
        );

        // ── lista: thumbnail direita, texto esquerda ──────────────────────────
        if (!empty($news)) {
            $html .= '<ul class="pgo-news__list">';
            foreach ($news as $item) {
                $thumb = $item->image
                    ? sprintf('<img src="%s" alt="" class="pgo-news__thumb" loading="lazy">', esc_url($item->image))
                    : '';

                $html .= sprintf(
                    '<li class="pgo-news__item">
                        <a href="%s" target="_blank" rel="noopener noreferrer" class="pgo-news__item-link">
                            <div class="pgo-news__item-content">
                                <h3 class="pgo-news__item-title">%s</h3>
                                <p class="pgo-news__item-excerpt">%s</p>
                                <time class="pgo-news__item-date">%s</time>
                            </div>
                            %s
                        </a>
                    </li>',
                    esc_url($item->link),
                    esc_html($item->title),
                    esc_html(wp_trim_words(wp_strip_all_tags($item->description), 15)),
                    esc_html($item->date),
                    $thumb,
                );
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}
