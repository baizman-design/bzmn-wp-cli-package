<?php

// open src/cli.php
// use reflection to read public method names (and @subcommand directives?)
// read in composer.json, convert to an object, update extra->commands, then save the file

namespace baizman_design_cli;

use ReflectionClass;
use ReflectionMethod;

$command_name = 'bzmn';
$grandparent_directory = dirname(
	path: __DIR__,
);
$command_path = sprintf( '%1$s/src/cli.php',
	$grandparent_directory,
);
$composer = sprintf( '%1$s/composer.json',
	$grandparent_directory,
);
$debugging_flags = [
	'-d',
	'--debug',
];

$debug = false;

if ( isset( $argv[1] ) && in_array( needle: $argv[1], haystack: $debugging_flags ) ) {
	$debug = true;
}

_debug(
	data: $command_name,
	label: '$command_name',
);

_debug(
	data: $command_path,
	label: '$command_path',
);

_debug(
	data: $composer,
	label: '$composer',
);

if ( file_exists( filename: $command_path ) ) {
	require_once $command_path;
} else {
	_die(
		message: sprintf( 'Could not load command file "%1$s".',
			$command_path,
		),
	);
}

$reflection_object = new ReflectionClass( objectOrClass: cli::class );

_debug(
	data: $reflection_object,
	label: '$reflection_object',
);

// retrieve public methods.
$public_methods = $reflection_object->getMethods( filter: ReflectionMethod::IS_PUBLIC );

_debug(
	data: $public_methods,
	label: '$public_methods',
);

// convert method names to command names. replace "-" with "_".
$method_name_replacements = [
	'_' => '-',
];
$command_names = array_map(
	callback: fn( $method ) => sprintf( '%1$s %2$s',
		$command_name,
		strtr( $method->getName(), $method_name_replacements ),
	),
	array: $public_methods,
);

// alphabetize the commands.
sort(
	array: $command_names,
);

_debug(
	data: $command_names,
	label: '$command_names',
);

// get composer file data.
if ( ! $json = file_get_contents( $composer ) ) {
	_die(
		message: sprintf( 'Could not load Composer file "%1$s".',
			$composer,
		),
	);
}

// convert json data into a PHP object.
if ( ! $composer_data = json_decode( $json ) ) {
	_die(
		message: sprintf( 'Could not decode JSON: %1$s',
			$json,
		),
	);
}

_debug(
	data: $composer_data,
	label: '$composer_data (before)',
);

// redefine "commands" section.
$composer_data->extra->commands = $command_names;

_debug(
	data: $composer_data,
	label: '$composer_data (after)',
);

$json_to_save = json_encode(
	value: $composer_data,
	flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, // pretty print, and don't escape slashes.
);

_debug(
	data: $json_to_save,
	label: '$json_to_save',
);

// write data back to composer.json.
if ( ! file_put_contents(
		filename: $composer,
		data: $json_to_save,
	)
) {
	_die(
		message: sprintf( 'Could not save updated JSON to Composer file "%1$s".',
			$composer,
		),
	);
}
// success. exit 0.
_die(
	message: sprintf( 'Successfully updated "%1$s".',
		$composer,
	),
	exit_code: 0,
);

/**
 * Debug print.
 *
 * @param string $label
 * @param mixed $data
 *
 * @return void
 */
function _debug(
	mixed $data,
	string $label = '',
):void
{
	global $debug;
	if ( $debug ) {
		if ( $label ) {
			printf( '%1$s: ',
				$label,
			);
		}
		print_r(
			value: $data
		);
		echo PHP_EOL;
	}
}

/**
 * Quit the program via outputting a message and exiting with a status code (default: 1).
 *
 * @param string $message
 * @param int $exit_code
 *
 * @return void
 */
function _die (
	string $message,
	int $exit_code = 1,
):void
{
	print( $message . PHP_EOL );
	exit( $exit_code );
}
