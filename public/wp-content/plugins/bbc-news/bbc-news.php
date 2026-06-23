<?php

/**
 * Plugin Name:  BBC News
 * Description:  Registers the "bbc" strategy — fetches news from BBC News RSS feed.
 * Version:      1.0.0
 * Requires PHP: 8.1
 * Text Domain:  bbc-news
 */

declare(strict_types=1);

use BbcNews\Providers\BbcNewsServiceProvider;
use CoreTheme\Bootstrap\Application;

add_action('core_theme_register_providers', static function (Application $app): void {
    $app->registerProvider(new BbcNewsServiceProvider());
}, 9);
