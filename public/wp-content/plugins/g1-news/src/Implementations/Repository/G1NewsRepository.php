<?php

declare(strict_types=1);

namespace G1News\Implementations\Repository;

use CoreTheme\Contracts\NewsRepositoryInterface;

/**
 * Repositório da estratégia G1 — consome o feed RSS do G1/Globo.
 *
 * Usa fetch_feed() do WordPress (SimplePie integrado) que já faz cache
 * automático via transients. Implementa NewsRepositoryInterface diretamente,
 * sem herdar de nenhuma classe base do core-theme.
 */
final class G1NewsRepository implements NewsRepositoryInterface
{
    private const FEED_URL = 'https://g1.globo.com/rss/g1/';

    public function findAll(array $args = []): array
    {
        // fetch_feed() retorna um objeto SimplePie ou WP_Error em falha de rede.
        $feed = fetch_feed(self::FEED_URL);

        if (is_wp_error($feed)) {
            return [];
        }

        $limit = (int) ($args['postsPerPage'] ?? 6);

        return array_map(
            fn($item) => $this->mapItem($item),
            $feed->get_items(0, $limit),
        );
    }

    public function findById(int $id): ?object
    {
        return null; // feeds RSS não têm busca por ID
    }

    public function count(array $args = []): int
    {
        $feed = fetch_feed(self::FEED_URL);
        return is_wp_error($feed) ? 0 : $feed->get_item_quantity();
    }

    /** Normaliza um item do SimplePie para o formato esperado pelos renderers. */
    private function mapItem(object $item): object
    {
        return (object) [
            'title'       => $item->get_title() ?? '',
            'link'        => $item->get_permalink() ?? '#',
            'description' => $item->get_description() ?? '',
            'date'        => $item->get_date('d/m/Y H:i') ?? '',
            'image'       => $this->extractImage($item),
        ];
    }

    /**
     * Tenta extrair a URL da imagem do item em três lugares diferentes,
     * pois feeds RSS expõem imagens de formas variadas.
     */
    private function extractImage(object $item): ?string
    {
        // 1. media:thumbnail (comum em feeds de portais de notícia)
        $thumb = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
        if (!empty($thumb[0]['attribs']['']['url'])) {
            return $thumb[0]['attribs']['']['url'];
        }

        // 2. media:content (variação do media RSS)
        $media = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
        if (!empty($media[0]['attribs']['']['url'])) {
            return $media[0]['attribs']['']['url'];
        }

        // 3. enclosure (padrão RSS 2.0 para anexos de mídia)
        $enclosure = $item->get_enclosure();
        if ($enclosure && str_starts_with((string) $enclosure->get_type(), 'image/')) {
            return $enclosure->get_link();
        }

        return null;
    }
}
