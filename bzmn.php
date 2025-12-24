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
$long_description = $short_description;
$long_description .= ' ';
$long_description .= 'Written by Saul Baizman, Baizman Design (https://baizmandesign.com).';

if ( ! class_exists( class: '\WP_CLI' ) ) {
	return;
}

$wp_cli_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $wp_cli_autoloader ) ) {
	require_once $wp_cli_autoloader;
}
$wp_cli_command = 'bzmn';
try {
	WP_CLI::add_command(
		name: $wp_cli_command,
		callable: cli::class,
		args: [
			'shortdesc' => $short_description,
			'longdesc'  => $long_description ?? $short_description,
		],
	);
} catch ( \Exception $e ) {
	WP_CLI::error( sprintf( 'could not add command "%1$s": %2$s',
		$wp_cli_command,
		$e->getMessage(),
	));
}
