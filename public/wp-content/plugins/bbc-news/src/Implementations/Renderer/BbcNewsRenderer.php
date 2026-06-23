<?php

declare(strict_types=1);

namespace BbcNews\Implementations\Renderer;

use CoreTheme\Contracts\NewsRendererInterface;

/**
 * Layout BBC: header com branding escuro, top story com imagem grande à esquerda
 * + corpo à direita, depois lista de secondary stories sem imagem (texto + linha).
 */
final class BbcNewsRenderer implements NewsRendererInterface
{
    public function render(array $news, array $attributes): string
    {
        if (empty($news)) {
            return '<p class="bbc-news__empty">No news found.</p>';
        }

        $topStory   = array_shift($news);
        $html       = '<div class="bbc-news">';

        // ── header com branding ───────────────────────────────────────────────
        $html .= '<header class="bbc-news__header">
                    <span class="bbc-news__brand">BBC</span>
                    <span class="bbc-news__brand-label">News</span>
                  </header>';

        // ── top story: imagem esquerda + texto direita ────────────────────────
        $topImage = $topStory->image
            ? sprintf('<img src="%s" alt="" class="bbc-news__top-img">', esc_url($topStory->image))
            : '';

        $html .= sprintf(
            '<article class="bbc-news__top-story">
                <a href="%s" target="_blank" rel="noopener noreferrer" class="bbc-news__top-link">
                    <figure class="bbc-news__top-figure">%s</figure>
                    <div class="bbc-news__top-body">
                        <h2 class="bbc-news__top-title">%s</h2>
                        <p class="bbc-news__top-excerpt">%s</p>
                        <time class="bbc-news__top-date">%s</time>
                    </div>
                </a>
            </article>',
            esc_url($topStory->link),
            $topImage,
            esc_html($topStory->title),
            esc_html(wp_trim_words(wp_strip_all_tags($topStory->description), 30)),
            esc_html($topStory->date),
        );

        // ── secondary stories: lista texto puro com divisor ───────────────────
        if (!empty($news)) {
            $html .= '<ul class="bbc-news__secondary">';
            foreach ($news as $item) {
                $html .= sprintf(
                    '<li class="bbc-news__story">
                        <a href="%s" target="_blank" rel="noopener noreferrer" class="bbc-news__story-link">
                            <h3 class="bbc-news__story-title">%s</h3>
                            <time class="bbc-news__story-date">%s</time>
                        </a>
                    </li>',
                    esc_url($item->link),
                    esc_html($item->title),
                    esc_html($item->date),
                );
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}
