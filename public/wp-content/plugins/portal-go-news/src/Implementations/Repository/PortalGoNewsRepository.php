<?php

declare(strict_types=1);

namespace PortalGo\Implementations\Repository;

use CoreTheme\Contracts\NewsRepositoryInterface;
use DateTimeImmutable;

/**
 * Repositório da estratégia Portal GO — consome a REST API do WordPress do portal.
 *
 * Ao contrário dos outros repositórios que usam RSS/SimplePie, este faz uma
 * chamada HTTP à WP REST API e desserializa o JSON manualmente.
 * O parâmetro _embed=wp:featuredmedia inclui a imagem destacada na mesma
 * resposta, evitando uma segunda requisição por post.
 */
final class PortalGoNewsRepository implements NewsRepositoryInterface
{
    private const API_URL = 'https://portalgo.com.br/wp-json/wp/v2/posts';

    public function findAll(array $args = []): array
    {
        $limit = (int) ($args['postsPerPage'] ?? 6);

        // _embed=wp:featuredmedia traz a imagem destacada embutida na resposta.
        $response = wp_remote_get(add_query_arg([
            'per_page' => $limit,
            '_embed'   => 'wp:featuredmedia',
        ], self::API_URL));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return [];
        }

        $posts = json_decode(wp_remote_retrieve_body($response), false);

        if (!is_array($posts)) {
            return [];
        }

        return array_map(fn($post) => $this->mapPost($post), $posts);
    }

    public function findById(int $id): ?object
    {
        return null;
    }

    public function count(array $args = []): int
    {
        return 0;
    }

    /** Normaliza um post da WP REST API para o formato esperado pelos renderers. */
    private function mapPost(object $post): object
    {
        // A imagem vem dentro do objeto _embedded injetado pelo ?_embed.
        $image = $post->_embedded->{'wp:featuredmedia'}[0]->source_url ?? null;

        $date = '';
        if (!empty($post->date)) {
            try {
                $date = (new DateTimeImmutable($post->date))->format('d/m/Y H:i');
            } catch (\Throwable) {
                $date = $post->date;
            }
        }

        return (object) [
            'title'       => wp_strip_all_tags($post->title->rendered ?? ''),
            'link'        => $post->link ?? '#',
            'description' => $post->excerpt->rendered ?? '',
            'date'        => $date,
            'image'       => $image,
        ];
    }
}
