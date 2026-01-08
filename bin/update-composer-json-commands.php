<?php

// open src/cli.php
// use reflection to read public method names (and @subcommand directives?)
// read in composer.json, convert to an object, update extra->commands, then save the file

namespace baizman_design_cli;

use ReflectionClass;
use ReflectionMethod;

$command_name = 'bzmn';
$command_path = dirname(__DIR__) . '/src/cli.php';
$composer = dirname(__DIR__) . '/composer.json';
$debugging_flags = [
	'-d',
	'--debug',
];

$debug = false;

if ( isset( $argv[1] ) && in_array( $argv[1], $debugging_flags ) ) {
	$debug = true;
}

dprint(
	data: $command_name,
	label: '$command_name',
);

dprint(
	data: $command_path,
	label: '$command_path',
);

dprint(
	data: $composer,
	label: '$composer',
);

if ( file_exists( $command_path ) ) {
	require_once $command_path;
} else {
	exit( 1 );
}

$reflection_object = new ReflectionClass( objectOrClass: cli::class );

dprint(
	data: $reflection_object,
	label: '$reflection_object',
);

// retrieve public methods.
$public_methods = $reflection_object->getMethods( filter: ReflectionMethod::IS_PUBLIC );

dprint(
	data: $public_methods,
	label: '$public_methods',
);

// convert method names to command names.
// replace "-" with "_".
$command_names = array_map(
	callback: fn( $method ) => $command_name . ' ' . str_replace( search: '_', replace: '-', subject: $method->getName() ),
	array: $public_methods,
);

// alphabetize the commands.
sort($command_names);

dprint(
	data: $command_names,
	label: '$command_names',
);

// get composer file data.
if ( ! $json = file_get_contents( $composer ) ) {
	exit( 1 );
}

// convert json data into a PHP object.
if ( ! $composer_data = json_decode( $json ) ) {
	exit( 1 );
}

dprint(
	data: $composer_data,
	label: '$composer_data (before)',
);

// redefine "commands" section.
$composer_data->extra->commands = $command_names;

dprint(
	data: $composer_data,
	label: '$composer_data (after)',
);

$json_to_save = json_encode(
	value: $composer_data,
	flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, // pretty print, and don't escape slashes.
);

dprint(
	data: $json_to_save,
	label: '$json_to_save',
);

// write data back to composer.json.
if ( ! file_put_contents(
		filename: $composer,
		data: $json_to_save,
	)
) {
	exit( 1 );
}
exit( 0 );

/**
 * Debug print.
 *
 * @param string $label
 * @param mixed $data
 *
 * @return void
 */
function dprint(
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
