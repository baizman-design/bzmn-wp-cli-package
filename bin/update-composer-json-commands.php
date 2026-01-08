<?php

// open src/cli.php
// use reflection to read public method names (and @subcommand directives?)
// read in composer.json, convert to an object, update extra->commands, then save the file

namespace baizman_design_cli;

use ReflectionClass;

$command_name = 'bzmn';
$command_path = dirname(__DIR__) . '/src/cli.php';
$composer = dirname(__DIR__) . '/composer.json';

#printf( '$command_path: %s' . PHP_EOL, $command_path );
if ( file_exists( $command_path ) ) {
	require_once $command_path;
}

$reflection_object = new ReflectionClass( cli::class );

#var_dump($reflection_object);

// retrieve public methods.
$public_methods = $reflection_object->getMethods(\ReflectionMethod::IS_PUBLIC);

#var_dump($public_methods);

// convert method names to command names.
$command_names = array_map(
	callback: fn( $method ) => $command_name . ' ' . str_replace( search: '_', replace: '-', subject: $method->getName() ),
	array: $public_methods,
);

// alphabetize the commands.
sort($command_names);

#var_dump($command_names);

// get composer file data.
$json = file_get_contents($composer);

// convert json data into a PHP object.
$composer_data = json_decode($json);

#var_dump($composer_data);

// redefine "commands" section.
$composer_data->extra->commands = $command_names;

#var_dump($composer_data);

// write data back to composer.json.
file_put_contents(
	filename: $composer,
	data: json_encode(
		value: $composer_data,
		flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, // pretty print, and don't escape slashes.
	)
);
