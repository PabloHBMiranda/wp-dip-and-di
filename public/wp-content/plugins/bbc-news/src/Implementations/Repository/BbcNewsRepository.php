<?php

declare(strict_types=1);

namespace BbcNews\Implementations\Repository;

use CoreTheme\Contracts\NewsRepositoryInterface;

final class BbcNewsRepository implements NewsRepositoryInterface
{
    private const FEED_URL = 'https://feeds.bbci.co.uk/news/rss.xml';

    public function findAll(array $args = []): array
    {
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
        return null;
    }

    public function count(array $args = []): int
    {
        $feed = fetch_feed(self::FEED_URL);

        return is_wp_error($feed) ? 0 : $feed->get_item_quantity();
    }

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

    private function extractImage(object $item): ?string
    {
        $thumb = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
        if (!empty($thumb[0]['attribs']['']['url'])) {
            return $thumb[0]['attribs']['']['url'];
        }

        $media = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
        if (!empty($media[0]['attribs']['']['url'])) {
            return $media[0]['attribs']['']['url'];
        }

        $enclosure = $item->get_enclosure();
        if ($enclosure && str_starts_with((string) $enclosure->get_type(), 'image/')) {
            return $enclosure->get_link();
        }

        return null;
    }
}
