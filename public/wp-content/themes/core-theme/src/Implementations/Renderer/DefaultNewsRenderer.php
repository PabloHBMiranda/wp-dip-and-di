<?php

declare(strict_types=1);

namespace CoreTheme\Implementations\Renderer;

use CoreTheme\Contracts\NewsRendererInterface;

final class DefaultNewsRenderer implements NewsRendererInterface
{
    public function render(array $news, array $attributes): string
    {
        if (empty($news)) {
            return '<p class="news-list__empty">' . esc_html__('No news found.', 'core-theme') . '</p>';
        }

        $html = '<ul class="news-list news-list--default">';

        foreach ($news as $post) {
            $permalink = get_permalink($post->ID);
            $title     = get_the_title($post->ID);
            $excerpt   = get_the_excerpt($post->ID);
            $thumb     = get_the_post_thumbnail($post->ID, 'medium');

            $html .= sprintf(
                '<li class="news-list__item">
                    <a href="%s" class="news-list__link">
                        %s
                        <h3 class="news-list__title">%s</h3>
                        <p class="news-list__excerpt">%s</p>
                    </a>
                </li>',
                esc_url($permalink),
                $thumb,
                esc_html($title),
                esc_html($excerpt),
            );
        }

        $html .= '</ul>';

        return $html;
    }
}
