<?php

/**
 * Plugin Name:  G1 News
 * Description:  Registers the "g1" strategy — fetches news from G1/Globo RSS feed.
 * Version:      1.0.0
 * Requires PHP: 8.1
 * Text Domain:  g1-news
 */

declare(strict_types=1);

use CoreTheme\Bootstrap\Application;
use G1News\Providers\G1NewsServiceProvider;

// O hook 'core_theme_register_providers' é disparado pelo tema (after_setup_theme, prio 5).
// Plugins SEMPRE se conectam aqui — nunca carregam o autoloader nem tocam no container.
// As classes só são resolvidas quando a callback executa, momento em que o autoloader já está ativo.
add_action('core_theme_register_providers', static function (Application $app): void {
    $app->registerProvider(new G1NewsServiceProvider());
}, 9);
