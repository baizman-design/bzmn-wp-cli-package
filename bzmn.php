<?php
/**
 * Plugin Name: BZMN WP CLI Package
 * Plugin URI:  https://github.com/baizman-design/bzmn-wp-cli-package
 * Description: A WP CLI package for multiple clients.
 * Version:     1.0.0
 * Author:      Saul Baizman
 * Author URI:  https://baizmandesign.com
 */

namespace baizman_design_cli;

use WP_CLI;

$short_description = 'A WP CLI package for multiple clients.';

if ( ! class_exists( class: '\WP_CLI' ) ) {
	return;
}

$wp_cli_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $wp_cli_autoloader ) ) {
	require_once $wp_cli_autoloader;
}

WP_CLI::add_command(
	name: 'bzmn',
	callable: cli::class,
	args: [
		'shortdesc' => $short_description,
		'longdesc' => $long_description ?? $short_description,
	],
);
