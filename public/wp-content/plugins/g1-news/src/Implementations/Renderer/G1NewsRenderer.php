<?php

declare(strict_types=1);

namespace G1News\Implementations\Renderer;

use CoreTheme\Contracts\NewsRendererInterface;

/**
 * Layout editorial G1: hero full-width + grade de cards abaixo.
 * Cada card tem imagem no topo, chapéu vermelho, título e data.
 */
final class G1NewsRenderer implements NewsRendererInterface
{
    public function render(array $news, array $attributes): string
    {
        if (empty($news)) {
            return '<p class="g1-news__empty">Nenhuma notícia encontrada.</p>';
        }

        $hero = array_shift($news);
        $html = '<div class="g1-news">';

        // ── hero ──────────────────────────────────────────────────────────────
        $heroImage = $hero->image
            ? sprintf('<img src="%s" alt="" class="g1-news__hero-img">', esc_url($hero->image))
            : '';

        $html .= sprintf(
            '<article class="g1-news__hero">
                <a href="%s" target="_blank" rel="noopener noreferrer" class="g1-news__hero-link">
                    %s
                    <div class="g1-news__hero-body">
                        <span class="g1-news__label">G1</span>
                        <h2 class="g1-news__hero-title">%s</h2>
                        <p class="g1-news__hero-excerpt">%s</p>
                        <time class="g1-news__hero-date">%s</time>
                    </div>
                </a>
            </article>',
            esc_url($hero->link),
            $heroImage,
            esc_html($hero->title),
            esc_html(wp_trim_words(wp_strip_all_tags($hero->description), 25)),
            esc_html($hero->date),
        );

        // ── grade de cards ────────────────────────────────────────────────────
        if (!empty($news)) {
            $html .= '<div class="g1-news__grid">';
            foreach ($news as $item) {
                $cardImage = $item->image
                    ? sprintf('<img src="%s" alt="" class="g1-news__card-img" loading="lazy">', esc_url($item->image))
                    : '<div class="g1-news__card-img-placeholder"></div>';

                $html .= sprintf(
                    '<article class="g1-news__card">
                        <a href="%s" target="_blank" rel="noopener noreferrer" class="g1-news__card-link">
                            %s
                            <div class="g1-news__card-body">
                                <span class="g1-news__label">G1</span>
                                <h3 class="g1-news__card-title">%s</h3>
                                <time class="g1-news__card-date">%s</time>
                            </div>
                        </a>
                    </article>',
                    esc_url($item->link),
                    $cardImage,
                    esc_html($item->title),
                    esc_html($item->date),
                );
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
