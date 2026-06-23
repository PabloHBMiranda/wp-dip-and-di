<?php

declare(strict_types=1);

use CoreTheme\Block\NewsListBlock;
use CoreTheme\Bootstrap\Application;
use CoreTheme\Providers\CoreNewsServiceProvider;

// ─── Autoloader ──────────────────────────────────────────────────────────────
// ABSPATH = /var/www/html/ → o vendor fica em /var/www/vendor/ (um nível acima).
// Todos os namespaces (CoreTheme\, G1News\, BbcNews\, PortalGo\…) estão mapeados
// neste único autoloader — nenhum plugin precisa de seu próprio vendor/.
require_once ABSPATH . '../vendor/autoload.php';

// ─── Bootstrap ───────────────────────────────────────────────────────────────
// Cria o singleton da Application (container DI + registry) e registra
// imediatamente a estratégia "default" fornecida pelo próprio tema.
$app = Application::getInstance();
$app->registerProvider(new CoreNewsServiceProvider());

// ─── Ponto de extensão para plugins e child themes ────────────────────────────
// Plugins devem chamar $app->registerProvider() dentro deste hook.
// Prioridade 5 garante que todos os providers estejam registrados
// antes do boot() rodar na prioridade 10.
//
// Exemplo em um plugin:
//   add_action('core_theme_register_providers', function (Application $app): void {
//       $app->registerProvider(new MinhaEstrategiaServiceProvider());
//   });
add_action('after_setup_theme', static function () use ($app): void {
    do_action('core_theme_register_providers', $app);
}, 5);

// ─── Boot ────────────────────────────────────────────────────────────────────
// Após todos os providers se registrarem, o boot() popula o registry com
// as StrategyStacks de cada um. A partir daqui o registry está pronto para uso.
add_action('after_setup_theme', static function () use ($app): void {
    $app->boot();
}, 10);

// ─── Registro do bloco ───────────────────────────────────────────────────────
// O container resolve o NewsListBlock injetando o NewsBlockRegistry automaticamente.
add_action('init', static function () use ($app): void {
    $block = $app->getContainer()->make(NewsListBlock::class);
    $block->register();
});

// ─── REST API — lista de estratégias para o editor de blocos ─────────────────
// O SelectControl do bloco consome este endpoint para listar as opções disponíveis.
// Público por design: a lista de estratégias não é dado sensível.
add_action('rest_api_init', static function () use ($app): void {
    register_rest_route('core-theme/v1', '/news-block/strategies', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => static fn() => rest_ensure_response(
            $app->getRegistry()->getAvailableStrategies()
        ),
        'permission_callback' => '__return_true',
    ]);
});
