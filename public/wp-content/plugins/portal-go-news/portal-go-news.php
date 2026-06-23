<?php

/**
 * Plugin Name:  Portal GO News
 * Description:  Registers the "portal-go" strategy — fetches news from Portal GO via WP REST API.
 * Version:      1.0.0
 * Requires PHP: 8.1
 * Text Domain:  portal-go-news
 */

declare(strict_types=1);

use CoreTheme\Bootstrap\Application;
use PortalGo\Providers\PortalGoNewsServiceProvider;

add_action('core_theme_register_providers', static function (Application $app): void {
    $app->registerProvider(new PortalGoNewsServiceProvider());
}, 9);
