<?php

declare ( strict_types = 1 );

$wpRoot = getenv ( 'WP_ROOT' );

if ( ! $wpRoot ) {
    fwrite ( STDERR, "Missing WP_ROOT env var ( path to WordPress root )\n" );
    exit ( 1 );
}

$wpLoad = rtrim ( $wpRoot, '/\\' ) . '/wp-load.php';

if ( ! file_exists ( $wpLoad ) ) {
    fwrite ( STDERR, "Could not find wp-load.php at: {$wpLoad}\n" );
    exit ( 1 );
}

/**
 * Boot WordPress.
 *
 * This uses your existing ddev WordPress install, not a separate test install.
 * That keeps the setup minimal and makes the first smoke test possible quickly.
 */
require_once $wpLoad;

/**
 * Load the plugin entrypoint directly.
 *
 * This avoids relying on whether the plugin is activated in wp-admin.
 * Replace `my-plugin.php` with your actual plugin main file.
 */
$pluginMainFile = dirname ( __DIR__ ) . '/email-encoder-bundle.php';

if ( ! file_exists ( $pluginMainFile ) ) {
    fwrite ( STDERR, "Could not find plugin main file at: {$pluginMainFile}\n" );
    exit ( 1 );
}

require_once $pluginMainFile;

/**
 * Load Composer autoload if plugin doesn't already do it.
 *
 * If your plugin main file already requires vendor/autoload.php, you can delete this block.
 */
$autoload = dirname ( __DIR__ ) . '/vendor/autoload.php';

if ( file_exists ( $autoload ) ) {
    require_once $autoload;
}

