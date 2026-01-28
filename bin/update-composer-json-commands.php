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
$force_flags = [
	'-f',
	'--force',
];

$debug = false;
$force = false;

if ( isset( $argv ) && array_intersect( $argv, $debugging_flags ) ) {
	$debug = true;
}

_debug(
	data: $debug ? 'true' : 'false',
	label: '$debug',
);

if ( isset( $argv ) && array_intersect( $argv, $force_flags ) ) {
	$force = true;
}

_debug(
	data: $force ? 'true' : 'false',
	label: '$force',
);

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

// note: this outputs nothing.
_debug(
	data: $reflection_object->getDocComment(),
	label: '$reflection_object->getDocComment()',
);

// retrieve public methods.
$public_methods = $reflection_object->getMethods( filter: ReflectionMethod::IS_PUBLIC );

_debug(
	data: $public_methods,
	label: '$public_methods',
);

$reflection_command_names = array_map(
	callback: fn( $method ) => sprintf( '%1$s %2$s',
		$command_name,
		_get_subcommand_directive(
			method: $method,
		),
	),
	array: $public_methods,
);

_debug(
	data: $reflection_command_names,
	label: '$reflection_command_names',
);

// alphabetize the commands.
sort(
	array: $reflection_command_names,
);

_debug(
	data: $reflection_command_names,
	label: '$reflection_command_names',
);

// manually prepend command name to array as first item.
array_unshift(
	$reflection_command_names,
	$command_name,
);

_debug(
	data: $reflection_command_names,
	label: '$reflection_command_names',
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

// skip if we're forcing it.
if ( ! $force ) {
	// have any methods been removed from the source but not composer.json?
	if ( $cruft = array_diff( $composer_data->extra->commands, $reflection_command_names ) ) {
		_die(
			message: sprintf( 'The following %4$s in %3$s %5$s not exist in %2$s: %1$s.',
				implode(
					separator: ', ',
					array: $cruft,
				),
				$command_path,
				$composer,
				count( $cruft ) == 1 ? 'command' : 'commands',
				count( $cruft ) == 1 ? 'does' : 'do',
			),
		);
	}
}

// skip if we're forcing it.
if ( ! $force ) {
	// have there been any changes? if not, quit.
	if ( ! array_diff( $reflection_command_names, $composer_data->extra->commands ) ) {
		_die(
			message: sprintf( 'There are no new public methods in "%1$s". Aborting.',
				$command_path,
			),
			exit_code: 0,
		);
	}
}
// redefine "commands" section.
$composer_data->extra->commands = $reflection_command_names;

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
$success_message = 'Successfully updated';
if ( $force ) {
	$success_message .= ' (by force)';
}
_die(
	message: sprintf( '%1$s "%2$s".',
		$success_message,
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
			value: $data,
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
	string $message = '',
	int $exit_code = 1,
):void
{
	// append newline.
	$message .= PHP_EOL;
	if ( $exit_code == 1 ) {
		$message = sprintf('ERROR: %1$s',
			$message,
		);
	}
	print( $message );
	exit( $exit_code );
}

/**
 * Return the "@subcommand" directive value in a DocBlock (or a default value).
 *
 * @param ReflectionMethod $method
 *
 * @return string
 */
function _get_subcommand_directive(
	ReflectionMethod $method,
):string {
	$docblock = $method->getDocComment();
	$method_name = $method->getName();
	// https://www.php.net/manual/en/reflectionclass.getdoccomment.php#118606
	$pattern = "# (@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
	preg_match_all(
		$pattern,
		$docblock,
		$matches,
		PREG_PATTERN_ORDER,
	);
	_debug(
		data: $matches,
		label: '$matches',
	);
	$directives = [];
	foreach ( $matches[1] as $directive_line ) {
		_debug(
			data: $directive_line,
			label: '$directive_line',
		);
		list( $directive, $value ) = explode(
			separator: ' ',
			string: $directive_line,
		);
		// TODO (maybe): identify potential duplicates and abort.
		$directives[$directive] = $value;
	}
	_debug(
		data: $directives,
		label: '$directives',
	);
	// if there's no @subcommand directive, fall back to the method name (with proper substitutions).
	return $directives['@subcommand'] ?? str_replace(
		search: '_',
		replace: '-',
		subject: $method_name,
	);
}
