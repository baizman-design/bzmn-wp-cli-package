<?php
/**
 * CLI class.
 */

namespace baizman_design_cli;

use WP_CLI;
use WP_CLI\ExitException;
use WP_Query;

final class cli {

	// flag to set whether the changes are made.
	private bool $dry_run = false;

	// flag to set porcelain mode.
	private bool $porcelain = false;

	// WP CLI command name for backup and restore commands.
	private string $ai1wm_command = 'ai1wm';

	// plugins to activate / deactivate.
	private array $ai1wm_plugin_slugs = [
		'all-in-one-wp-migration/all-in-one-wp-migration.php',
		'all-in-one-wp-migration-multisite-extension/all-in-one-wp-migration-multisite-extension.php',
	];

	/**
	 * Flush URL rewrite rules.
	 *
	 * ## OPTIONS
	 *
	 * [--hard]
	 * : Hard flush. Updates .htaccess.
	 *
	 * ## EXAMPLES
	 *
	 *     # Soft-flush the rewrite rules.
	 *     $ wp bzmn flush-rewrite-rules
	 *     Success: The rewrite rules have been soft-flushed.
	 *
	 *     # Hard-flush the rewrite rules.
	 *     $ wp bzmn flush-rewrite-rules --hard
	 *     Success: The rewrite rules have been hard-flushed.
	 *
	 * @subcommand flush-rewrite-rules
	 * @alias flush_rewrite_rules
	 */
	public function flush_rewrite_rules(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		// https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
		$hard = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'hard',
			default: false,
		);
		flush_rewrite_rules( hard: $hard );
		WP_CLI::success (
			message: sprintf( 'The rewrite rules have been %1$s-flushed.',
				$hard ? 'hard' : 'soft',
			),
		);
	}

	/**
	 * Print the environment type.
	 *
	 * Displays the value of the WP_ENVIRONMENT_TYPE constant.
	 *
	 * ## OPTIONS
	 *
	 * [--porcelain]
	 * : Print just the environment name.
	 *
	 * ## EXAMPLES
	 *
	 *     # Print the environment type with a prefix.
	 *     $ wp bzmn print-wp-environment
	 *     environment: production
	 *
	 *     # Print the environment type without a prefix.
	 *     $ wp bzmn print-wp-environment --porcelain
	 *     production
	 *
	 * @subcommand print-wp-environment
	 * @alias print_wp_environment
	 */
	public function print_wp_environment(
		array $args = [],
		array $assoc_args = [],
    ):void
	{
		$porcelain = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'porcelain',
			default: false,
		);
		$prefix = $porcelain ? '' : 'environment: ';
		WP_CLI::log(
			message: sprintf( '%1$s%2$s',
				$prefix,
				wp_get_environment_type(),
			),
		);
	}

	/**
	 * Print custom post types.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Format result set. Options: table, json, csv, yaml. Default: table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *
	 * [--sort-by=<field>]
	 * : Sort results. Options: name, slug. Default: name.
	 * ---
	 * default: name
	 * options:
	 *   - name
	 *   - slug
	 *
	 * [--include_builtins]
	 * : Include built-in post types.
	 *
	 * ## EXAMPLES
	 *
	 *     # Print the custom post types sorted alphabetically by name.
	 *     wp bzmn print-custom-post-types
	 *
	 *     # Print the custom post types sorted alphabetically by slug.
	 *     wp bzmn print-custom-post-types --sort-by=slug
	 *
	 *     # Print the custom and built-in post types.
	 *     wp bzmn print-custom-post-types --include_builtins
	 *
	 *     # Print the custom and built-in post types sorted alphabetically by slug.
	 *     wp bzmn print-custom-post-types --include_builtins --sort-by=slug
	 *
	 * @subcommand print-custom-post-types
	 * @alias print_custom_post_types
	 */
	public function print_custom_post_types (
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$format = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'format',
			default: 'table',
		);
		$include_builtins = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'include_builtins',
			default: false,
		);
		$sort_by = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'sort-by',
			default: 'name', // post type label. corresponds to custom array in $post_types.
		);
		$get_post_types_args = [];
		if ( ! $include_builtins ) {
			$get_post_types_args = [
				'_builtin' => false,
			];
		}
		$custom_post_type_objects = get_post_types(
			args: $get_post_types_args,
			output: 'objects',
		);
		$post_types = [];
		// this structure simplifies the format of the array elements.
		array_map(
			callback: function( $custom_post_type ) use ( & $post_types, $include_builtins ){
				$post_type = [
					'name' => $custom_post_type->label,
					'slug' => $custom_post_type->name,
					'public?' => $custom_post_type->public ? 'X': '',
					'custom?' => $custom_post_type->_builtin ? '': 'X',
				];
				// exclude field if we're only printing custom post types.
				if ( ! $include_builtins ) {
					unset( $post_type['custom?'] );
				}
				$post_types[] = $post_type;
			},
			array: $custom_post_type_objects,
		);
		// sort $post_types by $sort_by value (default: "name").
		// can be either "name" or "slug."
		uasort(
			array: $post_types,
			callback: function( $a, $b ) use ( $sort_by ) {
				return strnatcasecmp( $a[$sort_by], $b[$sort_by] );
			}
		);
		WP_CLI\Utils\format_items(
			format: $format,
			items: $post_types,
			fields: array_keys( $post_types[0] ),
		);
	}

	/**
	 * Set pagination on Toolset > Post Types screen.
	 *
	 * This is a documented bug:
	 * https://toolset.com/errata/pagination-screen-option-on-post-type-listing-page-doesnt-work/
	 *
	 * ## OPTIONS
	 *
	 * <email>
	 * : The user's email.
	 *
	 * <pagination>
	 * : The pagination value.
	 *
	 * ## EXAMPLES
	 *
	 *     # Set the pagination for user@domain.com to 25 items per page.
	 *     wp bzmn set-toolset-post-type-pagination user@domain.com 25
	 *     Success: The pagination for "user@domain.com" was set to "25."
	 *
	 * @subcommand set-toolset-post-type-pagination
	 * @alias set_toolset_post_type_pagination
    */
	public function set_toolset_post_type_pagination(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		list ( $email, $pagination ) = $args ;
		// are the arguments the correct types?
		if ( is_string( $email ) && is_numeric( $pagination ) ) {
			// valid user ID?
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$option_name = 'wpcf_cpt_per_page';
				// get current option, if it exists.
				$current_pagination_value = get_user_option(
					option: $option_name,
					user: $user->ID
				);

				if ( $current_pagination_value == $pagination ) {
					WP_CLI::warning(sprintf('The pagination is already set to "%1$s."',
						$pagination,
					));
					exit;
				}

				$return = update_user_option(
					user_id: $user->ID,
					option_name: $option_name,
					newvalue: $pagination,
					is_global: true
				);
				if ( $return === false ) {
					// if the pagination value is already set, and is being set to the same value,
					// this will return false, which is not a true failure.
					WP_CLI::error( sprintf( 'The pagination for "%1$s" could not be set.',
						$email,
					));
				} else {
					WP_CLI::success( sprintf( 'The pagination for "%1$s" was set to "%2$s."',
						$email,
						$pagination,
					));
				}
			} else {
				WP_CLI::error(
					message: sprintf( 'The user "%1$s" is not a valid user.',
						$email,
					),
				);
			}
		} else {
			WP_CLI::error(
				message: 'The arguments were not valid.',
			);
		}
	}

    /**
     * Delete WooCommerce payment field style transients.
     *
     * ## EXAMPLES
     *
     *     # Delete the WooCommerce transients.
     *     wp bzmn delete-woocommerce-transients
     *     Success: Deleted WooCommerce payment field style transients.
     *
     * @subcommand delete-woocommerce-transients
     * @alias delete_woocommerce_transients
     */
    public function delete_woocommerce_transients():void
    {
        // https://woocommerce.com/document/woopayments/customization-and-translation/customize-payments-appearance/
		$transients = [
			'upe_process_redirect_order_id_mismatched',
			'wcpay_upe_appearance',
			'wcpay_upe_add_payment_method_appearance',
			'wcpay_wc_blocks_upe_appearance',
			'wcpay_upe_bnpl_product_page_appearance',
			'wcpay_upe_bnpl_classic_cart_appearance',
			'wcpay_upe_bnpl_cart_block_appearance',
			'wcpay_upe_appearance_theme',
			'wcpay_upe_add_payment_method_appearance_theme',
			'wcpay_wc_blocks_upe_appearance_theme',
			'wcpay_upe_bnpl_product_page_appearance_theme',
			'wcpay_upe_bnpl_classic_cart_appearance_theme',
			'wcpay_upe_bnpl_cart_block_appearance_theme',
			// https://woocommerce.com/document/stripe/customization/style-payment-form/
			// Shortcode checkout
			'wc_stripe_appearance',
	        // Block checkout
			'wc_stripe_blocks_appearance',
		];
		array_walk(
			$transients,
			function( $transient ) {
				delete_transient(
					transient: $transient,
				);
			}
		);
        WP_CLI::success(
			message: 'Deleted WooCommerce payment field style transients.',
        );
    }

	/**
	 * Update the value of a post meta field for a given post type.
	 * If the field is not present, the field and value will be added.
	 *
	 * ## OPTIONS
	 *
	 * <field>
	 * : The field to update.
	 *
	 * <value>
	 * : The field's new value.
	 *
	 * <post_type>
	 * : The post type.
	 *
	 * [--dry_run]
	 * : Dry run. Do not modify the database.
	 *
	 * ## EXAMPLES
	 *
	 *     # Set the value of "wpcf-hide-page-in-navigation" to "0" for all pages.
	 *     wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page
	 *
	 *     # Set the value of "wpcf-hide-page-in-navigation" to "0" for all pages in a test run.
	 *     wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page --dry_run
	 *
	 * @subcommand populate-post-meta
	 * @alias populate_post_meta
	 */
	public function populate_post_meta(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$dry_run = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'dry_run',
			default: false,
		);

		$this->dry_run = $dry_run;
		list ( $field, $value, $post_type ) = $args;

		$posts = new WP_Query([
			'post_type' => [$post_type],
			'posts_per_page' => -1, // infinite
			'fields' => 'ids',
			'post_status' => 'any',
		]);

		$post_count = $posts->post_count;

		if ( $post_count > 0 ) {
			if ( ! $this->dry_run ) {
				WP_CLI::confirm( sprintf( 'Are you sure you want to update %1$d posts?',
					$post_count,
				) );
			}

			$this->backup_database();

			WP_CLI::log(sprintf('Setting "%1$s" to "%2$s" for post type "%3$s"...',
				$field,
				$value,
				$post_type,
			));
			$post_counter = 1;
			foreach ( $posts->posts as $post_id ) {
				WP_CLI::log( sprintf( 'Updating post ID %1$d (%2$d/%3$d)...',
					$post_id,
					$post_counter,
					$post_count,
				));
				// update the post meta data.
				if ( ! $this->dry_run ) {
					update_post_meta(
						post_id: $post_id,
						meta_key: $field,
						meta_value: $value,
					);
				}
				$post_counter++;
			}
			if ( ! $this->dry_run ) {
				WP_CLI::success(
					message: sprintf( '%1$d posts were updated.',
						$post_counter-1,
					),
				);
			} else {
				WP_CLI::log(
					message: sprintf( '%1$d posts were not updated.',
						$post_counter-1,
					),
				);
			}
		} else {
			// zero posts found.
			WP_CLI::error(
				message: sprintf('There were no posts for the post type "%1$s."',
					$post_type,
				),
			);
		}
	}

	/**
	 * Toggle the boolean value of the WP_DEBUG constant in wp-config.php.
	 *
	 * ## EXAMPLES
	 *
	 *     # Toggle the WP_DEBUG constant value.
	 *     wp bzmn toggle-debug
	 *     Success: WP_DEBUG is set to true.
	 *
	 *     # Toggle the WP_DEBUG constant value.
	 *     wp bzmn toggle-debug
	 *     Success: WP_DEBUG is set to false.
	 *
	 * @when before_wp_load
	 * @subcommand toggle-debug
	 * @alias toggle_debug
	 */
	public function toggle_debug():void
	{
		$this->set_config_file_value(
			constant: 'WP_DEBUG',
		);
	}

	/**
	 * Toggle the boolean value of the WP_DEBUG_DISPLAY constant in wp-config.php.
	 *
	 * ## EXAMPLES
	 *
	 *     # Toggle the WP_DEBUG_DISPLAY constant value.
	 *     wp bzmn toggle-debug-display
	 *     Success: WP_DEBUG_DISPLAY is set to true.
	 *
	 *     # Toggle the WP_DEBUG_DISPLAY constant value.
	 *     wp bzmn toggle-debug-display
	 *     Success: WP_DEBUG_DISPLAY is set to false.
	 *
	 * @when before_wp_load
	 * @subcommand toggle-debug-display
	 * @alias toggle_debug_display
	 */
	public function toggle_debug_display():void
	{
		$this->set_config_file_value(
			constant: 'WP_DEBUG_DISPLAY',
		);
	}

	/**
	 * Back up a website using All-in-One WP Migration and Backup plugins.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Type of backup. Options: quick, full, sql. Default: quick.
	 * ---
	 * default: quick
	 * options:
	 *   - quick
	 *   - full
	 *   - sql
	 *
	 * [--porcelain]
	 * : Output just the backup filename.
	 *
	 * ## EXAMPLES
	 *
	 *     # Make a default (quick) backup.
	 *     wp bzmn backup
	 *
	 *     # Make a quick (database) backup.
	 *     wp bzmn backup --type=quick
	 *
	 *     # Make a full (file and database) backup.
	 *     wp bzmn backup --type=full
	 *
	 *     # Make a database (SQL) backup.
	 *     wp bzmn backup --type=sql
	 *
	 *     # Make a quick (database) backup, outputting only the filename.
	 *     wp bzmn backup --type=quick --porcelain
	 *
     * @subcommand backup
     * @alias bu
	 */
	public function backup(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$type = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'type',
			default: 'quick',
		);
		$porcelain = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'porcelain',
			default: $this->porcelain,
		);
		// set class property. needed when backup_database() is called.
		$this->porcelain = $porcelain;
		// mysql dump.
		if ( $type == 'sql' ) {
			$this->backup_database( file: sprintf('%1$s/%2$s-%3$s-export.sql',
				untrailingslashit( ABSPATH ),
				DB_NAME,
				date( 'YmdHis' ),
			));
			if ( ! $porcelain ) {
				WP_CLI::success(
					message: sprintf('%1$s backup succeeded.',
						ucfirst( $type ),
					),
				);
			}
			// we're done here.
			WP_CLI::halt(
				return_code: 0,
			);
		}
		$ai1wm_plugins = $this->get_plugin_dirs();
		$this->plugin_presence_check();
		// determine whether the plugins need to be activated.
		$plugins_need_to_be_activated = false;
		foreach ( $this->ai1wm_plugin_slugs as $plugin ) {
			$is_plugin_active = is_plugin_active( $plugin );
			WP_CLI::debug(
				message: sprintf( '%1$s is active: %2$s',
					$plugin,
					$is_plugin_active ? 'true' : 'false',
				),
			);
			if ( ! $is_plugin_active ) {
				$plugins_need_to_be_activated = true;
				break;
			}
		}
		WP_CLI::debug(
			message: sprintf( '$plugins_need_to_be_activated: %1$s',
				$plugins_need_to_be_activated ? 'true' : 'false',
			),
		);
		$wp_path = WP_CLI::get_config(
			key: 'path',
		) ?? '.';
		// arguments for quick backup, sans leading double-dashes ("--").
		$quick_backup_args = [
			'exclude-spam-comments',
			'exclude-post-revisions',
			'exclude-media',
			'exclude-themes',
			'exclude-inactive-themes',
			'exclude-muplugins',
			'exclude-plugins',
			'exclude-inactive-plugins',
			'exclude-cache',
			'exclude-email-replace',
		];
		// prepend double-dash to all quick backup arguments.
		$quick_backup_args = array_map(
			callback: fn ( $arg ) => sprintf( '--%1$s',
				$arg,
			),
			array: $quick_backup_args,
		);
		// default parameters to WP_CLI::runcommand().
		$runcommand_option_defaults = [
			'return' => true,  // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
			'command_args' => [ sprintf( '--path=%1$s', $wp_path ), ], // add path (necessary when an alias is used).
		];
		// is this a multisite installation? if so, add flag for activate / deactivate commands.
		$network_flag = is_multisite() ? '--network' : '';
		// if the necessary plugins were not active, activate them.
		if ( $plugins_need_to_be_activated ) {
			$plugin_activate_message = $this->activate_ai1wm_plugins(
				ai1wm_plugins: $ai1wm_plugins,
				runcommand_option_defaults: $runcommand_option_defaults,
				network_flag: $network_flag,
			);
			$has_command_return_options = wp_parse_args(
				args: [
					'launch' => true,
					'exit_error' => false, // don't exit on error.
				],
				defaults: $runcommand_option_defaults,
			);
			$this->has_command(
				command_name: $this->ai1wm_command,
				runcommand_options: $has_command_return_options,
			);
			if ( ! $porcelain ) {
				WP_CLI::log(
					message: sprintf( '%1$s',
						$plugin_activate_message->stdout,
					),
				);
			}
		} else {
			if ( ! $porcelain ) {
				WP_CLI::warning(
					message: sprintf( 'The %1$s plugins are already activate. Continuing...',
						implode(
							separator: ' and ',
							array: $ai1wm_plugins,
						),
					),
				);
			}
		}
		$ai1wm_command_arguments = '';
		if ( $type == 'quick' ) {
			$ai1wm_command_arguments = implode(
				separator: ' ',
				array: $quick_backup_args,
			);
		}
		if ( ! $porcelain ) {
			WP_CLI::log(
				message: sprintf( 'Backup type: %1$s',
					$type,
				),
			);
			WP_CLI::log(
				message: sprintf( 'Backup site: %1$s',
					get_site_url(),
				),
			);
			WP_CLI::log(
				message: 'Starting backup...',
			);
		}
		$backup_command_return_options = wp_parse_args (
			args: [
				'return' => 'all', // return all information.
				'launch' => true, // run in new process because we've modified the WordPress environment when we activated plugins.
				'exit_error' => false, // don't quit on error.
			],
			defaults: $runcommand_option_defaults,
		);
		$backup_command_return_message = WP_CLI::runcommand(
			command: sprintf( '%1$s backup %2$s',
				$this->ai1wm_command,
				$ai1wm_command_arguments,
			),
			options: $backup_command_return_options,
		);
		if ( $backup_command_return_message->return_code == '1' ) {
			if ( $backup_command_return_message->stdout ) {
				WP_CLI::error(
					message: $backup_command_return_message->stdout,
					exit: false,
				);
			}
			if ( $backup_command_return_message->stderr ) {
				WP_CLI::log(
					message: $backup_command_return_message->stderr,
				);
			}
			WP_CLI::halt(
				return_code: 1,
			);
		} else {
			if ( ! $porcelain ) {
				WP_CLI::log(
					message: $backup_command_return_message->stdout,
				);
			}
			// get just the filename.
			if ( $porcelain ) {
				$backup_command_return_message_lines = explode(
					separator: PHP_EOL,
					string: $backup_command_return_message->stdout,
				);
				list ( , $wpress_filename, ) = explode(
					separator: ': ',
					string: $backup_command_return_message_lines[3],
				);
				WP_CLI::log(
					message: $wpress_filename,
				);
			}
		}
		// don't deactivate the plugins if they were already active.
		if ( $plugins_need_to_be_activated ) {
			$plugin_deactivate_message = $this->deactivate_ai1wm_plugins(
				ai1wm_plugins: $ai1wm_plugins,
				runcommand_option_defaults: $runcommand_option_defaults,
				network_flag: $network_flag,
			);
			if ( ! $porcelain ) {
				WP_CLI::log(
					message: sprintf( '%1$s',
						$plugin_deactivate_message->stdout,
					),
				);
			}
		} else {
			if ( ! $porcelain ) {
				WP_CLI::warning(
					message: sprintf( 'The %s plugins were already active and have not been deactivated.',
						implode(
							separator: ' and ',
							array: $ai1wm_plugins,
						),
					),
				);
			}
		}
		if ( ! $porcelain ) {
			WP_CLI::success(
				message: sprintf( '%1$s backup succeeded.',
					ucfirst( $type ),
				),
			);
		}
	}

	/**
	 * Restore WP instance from All-in-One WP Migration and Backup .wpress file.
	 *
	 * ## OPTIONS
	 *
	 * <wpress_backup_file>
	 * : .wpress backup file.
	 *
	 * ## EXAMPLES
	 *
	 *     # Restore website from a1b2c3.wpress.
	 *     wp bzmn restore a1b2c3.wpress
	 *
     * @subcommand restore
	 */
	public function restore(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		list( $wpress_backup_file, ) = $args;
		// just get the filename, no parent directory.
		$wpress_backup_file = basename( path: $wpress_backup_file );
		WP_CLI::debug(
			message: sprintf( '$wpress_backup_file: %1$s',
				$wpress_backup_file,
			)
		);
		$ai1wm_path_option_name = 'ai1wm_backups_path';
		$ai1wm_path = get_option(
			option: $ai1wm_path_option_name,
			default_value: false,
		);
		if ( $ai1wm_path ) {
			$ai1wm_backup_full_path = sprintf( '%1$s/%2$s',
				$ai1wm_path,
				$wpress_backup_file,
			);
			if ( ! file_exists( filename: $ai1wm_backup_full_path ) ) {
				WP_CLI::error(
					message: sprintf( 'The backup file "%1$s" in the backup directory "%2$s" does not exist.',
						$wpress_backup_file,
						$ai1wm_path,
					),
				);
			}
		} else {
			WP_CLI::error(
				message: sprintf( 'The database option with the backup directory location, "%1$s," does not exist.',
					$ai1wm_path_option_name,
				),
			);
		}
		$ai1wm_plugins = $this->get_plugin_dirs();
		$this->plugin_presence_check();
		// determine whether the plugins need to be activated.
		$plugins_need_to_be_activated = false;
		foreach ( $this->ai1wm_plugin_slugs as $plugin ) {
			$is_plugin_active = is_plugin_active( $plugin );
			WP_CLI::debug(
				message: sprintf( '%1$s is active: %2$s',
					$plugin,
					$is_plugin_active ? 'true' : 'false',
				)
			);
			if ( ! $is_plugin_active ) {
				$plugins_need_to_be_activated = true;
				break;
			}
		}
		WP_CLI::debug(
			message: sprintf( '$plugins_need_to_be_activated: %1$s',
				$plugins_need_to_be_activated ? 'true' : 'false',
			),
		);
		$wp_path = WP_CLI::get_config( key: 'path' ) ?? '.';
		// default parameters to WP_CLI::runcommand().
		$runcommand_option_defaults = [
			'return' => true,  // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
			'command_args' => [ sprintf( '--path=%1$s', $wp_path ), ], // add path (necessary when an alias is used).
		];
		// is this a multisite installation? if so, add flag for activate / deactivate commands.
		$network_flag = is_multisite() ? '--network' : '';
		if ( $plugins_need_to_be_activated ) {
			$plugin_activate_message = $this->activate_ai1wm_plugins(
				ai1wm_plugins: $ai1wm_plugins,
				runcommand_option_defaults: $runcommand_option_defaults,
				network_flag: $network_flag,
			);
			// TODO (maybe): remove has_command() method, which is used only once, and refactor below.
			$has_command_return_options['launch'] = true;
			$this->has_command(
				command_name: $this->ai1wm_command,
				runcommand_options: $has_command_return_options,
			);
			WP_CLI::log(
				message: sprintf( '%1$s',
					$plugin_activate_message->stdout,
				),
			);
		} else {
			WP_CLI::warning(
				message: sprintf( 'The %1$s plugins are already activate. Continuing...',
					implode(
						separator: ' and ',
						array: $ai1wm_plugins,
					),
				),
			);
		}
		$ai1wm_command_arguments = sprintf( '--yes %1$s',
			$wpress_backup_file,
		);
		$restore_command_return_options = wp_parse_args (
			args: [
				'launch' => true, // run in new process because we've modified the WordPress environment when we activated plugins.
			],
			defaults: $runcommand_option_defaults,
		);
		WP_CLI::log(
			message: sprintf( 'Restoring %1$s from %2$s...',
				get_site_url(),
				$wpress_backup_file,
			),
		);
		$return_message = WP_CLI::runcommand(
			command: sprintf( '%1$s restore %2$s',
				$this->ai1wm_command,
				$ai1wm_command_arguments,
			),
			options: $restore_command_return_options,
		);
		WP_CLI::log(
			message: sprintf( '%1$s',
				$return_message,
			),
		);
		// don't deactivate the plugins if they were already active.
		if ( $plugins_need_to_be_activated ) {
			$plugin_deactivate_message = $this->deactivate_ai1wm_plugins(
				ai1wm_plugins: $ai1wm_plugins,
				runcommand_option_defaults: $runcommand_option_defaults,
				network_flag: $network_flag,
			);
			WP_CLI::log(
				message: sprintf( '%1$s',
					$plugin_deactivate_message->stdout,
				),
			);
		} else {
			WP_CLI::warning(
				message: sprintf('The %1$s plugins were already active and have not been deactivated.',
					implode(
						separator: ' and ',
						array: $ai1wm_plugins,
					),
				),
			);
		}
		WP_CLI::success(
			message: 'Restore succeeded.',
		);
	}

	/**
	 * Clear the Sucuri firewall cache. Uses $SUCURI_API_KEY and $SUCURI_API_SECRET environment variables.
	 *
	 * ## OPTIONS
	 *
	 * [<filename>]
	 * : A filename to remove from the cache.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear the Sucuri cache for a domain.
	 *     wp bzmn clear-sucuri-cache
	 *
	 *     # Clear the Sucuri cache for the file index.php.
	 *     wp bzmn clear-sucuri-cache index.php
	 *
	 * @subcommand clear-sucuri-cache
	 * @alias clear_sucuri_cache
	 */
	public function clear_sucuri_cache (
		array $args = [],
		array $assoc_args = [],
	):void
	{
		list( $filename, ) = $args;
		$api_key = getenv(
			name: 'SUCURI_API_KEY',
		);
		if ( ! $api_key) {
			WP_CLI::error(
				message: 'Environment variable $SUCURI_API_KEY is missing.',
			);
		}
		$api_secret = getenv(
			name: 'SUCURI_API_SECRET',
		);
		if ( ! $api_secret) {
			WP_CLI::error(
				message: 'Environment variable $SUCURI_API_SECRET is missing.',
			);
		}
		$data = [
			'k' => $api_key, // key
			's' => $api_secret, // secret
			'a' => 'clear_cache', // action
		];
		$message = 'Attempting to clear the cache for' ;
		// append filename to array, if needed.
		if ( $filename ) {
			$data['file'] = $filename;
			$message .= sprintf(' "%1$s"',
				$filename,
			);
		} else {
			$message .= ' the entire domain';
		}
		// append ellipsis.
		$message .= '...';
		WP_CLI::log(
			message: $message,
		);
		// https://waf.sucuri.net/?settings&site=lifetimearts.org&panel=api
		$response = wp_remote_post (
			url: 'https://waf.sucuri.net/api?v2',
			args: [
				'body' => $data,
			],
		);
		$response_code = wp_remote_retrieve_response_code(
			response: $response,
		);
		$response_body = json_decode(
			json: wp_remote_retrieve_body( $response ),
			associative: true,
		);
		$response_message = implode(
			separator: ', ',
			array: $response_body['messages'],
		);
		// "1" is success, "0" for failure.
		$command_status = $response_body['status'];
		// note: errors also return a 200 status code.
		if ( $response_code == 200 && $command_status == '1' ) {
			WP_CLI::success(
				message: $response_message,
			);
		} else {
			WP_CLI::error(
				message: $response_message,
			);
		}
	}

	/**
	 * Clear the Cloudflare cache. Default: purge everything. Uses $CLOUDFLARE_API_KEY environment variable.
	 *
	 * API documentation: https://developers.cloudflare.com/api/resources/cache/
	 *
	 * ## OPTIONS
	 *
	 * <zone_id>
	 * : Cloudflare zone ID.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear the cloudflare cache for a domain.
	 *     wp bzmn clear-cloudflare-cache 12345
	 *     Success: The Cloudflare cache has been cleared.
	 *
	 * @subcommand clear-cloudflare-cache
	 * @alias clear_cloudflare_cache
	 */
	public function clear_cloudflare_cache(
		$args = [],
		$assoc_args = [],
	):void
	{
		list( $zone_id, ) = $args;
		$api_key = getenv(
			name: 'CLOUDFLARE_API_KEY',
		);
		if ( ! $api_key) {
			WP_CLI::error(
				message: 'Environment variable $CLOUDFLARE_API_KEY is missing.',
			);
		}
		$data = [
			'purge_everything' => true,
		];
		$clear_cache_url = sprintf( 'https://api.cloudflare.com/client/v4/zones/%1$s/purge_cache',
			$zone_id,
		);
		// the body must be JSON. see https://community.cloudflare.com/t/purge-everything-api-is-not-working/387799.
		$body = json_encode(
			value: $data,
		);
		$args = [
			'timeout' => 10,
			'body' => $body,
			'headers' => [
				'Authorization' => sprintf( 'Bearer %1$s',
					$api_key,
				),
				'Content-Type' => 'application/json',
			],
		];
		$response = wp_remote_post(
			url: $clear_cache_url,
			args: $args,
		);
		// the body returns a string containing JSON-encoded data.
		$response_body = json_decode(
			json: wp_remote_retrieve_body( response: $response ),
			associative: true,
		);
		$command_status = $response_body['success'] ?? false;
		if ( is_wp_error( thing: $response ) || wp_remote_retrieve_response_code( response: $response ) !== 200 || empty ( wp_remote_retrieve_body( response: $response ) ) || ! $command_status ) {
			WP_CLI::error(
				message: 'Failed to clear the Cloudflare cache.',
			);
			WP_CLI::debug(
				message: sprintf( 'Details: %1$s',
					print_r(
						value: $response,
						return: true
					),
				),
			);
		}
		if ( $command_status ) {
			WP_CLI::success(
				message: 'The Cloudflare cache has been cleared.',
			);
		} else {
			WP_CLI::error(
				message: 'An unknown error occurred while trying to clear the Cloudflare cache.',
			);
		}
	}

	/**
	 * Display the disk usage of WP_CONTENT_DIR.
	 *
	 * ## OPTIONS
	 *
	 * [--sort-by=<field>]
	 * : Sort directories in each subsection alphabetically or by size. Options: name, size. Default: name.
	 * ---
	 * default: name
	 * options:
	 *   - name
	 *   - size
	 *
	 * ## EXAMPLES
	 *
	 *     # Display the disk usage.
	 *     wp bzmn disk-usage
	 *     wp bzmn disk-usage --sort-by=size
	 *
	 * @subcommand disk-usage
	 * @alias du
	 */
	public function disk_usage(
		$args = [],
		$assoc_args = [],
	):void
	{
		$sort_by = WP_CLI\Utils\get_flag_value(
			assoc_args: $assoc_args,
			flag: 'sort-by',
			default: 'name', // directory name.
		);
		// section labels.
		$core_label = __( text: 'Core Directories' );
		$other_label = __( text: 'Other Directories' );
		$total_label = __( text: 'Totals' );
		// supported operating systems for "du" command.
	    $supported_systems = [
			'Darwin', // macOS
			'Linux',
			'FreeBSD',
		];
		// get this operating system.
		$os = php_uname(
			mode: 's',
		);
		if ( ! in_array ( needle: $os, haystack: $supported_systems ) ) {
			WP_CLI::error(
				message: sprintf( '"%1$s" is not a supported OS for this command.',
					$os,
				),
			);
		}
		$uploads = wp_get_upload_dir()['basedir'] ?? 'uploads';
		$core_directories_default = [
			// plugins.
			basename( path: WP_PLUGIN_DIR ),
			// themes.
			basename( path: dirname( get_stylesheet_directory() ) ),
			// uploads.
			basename( path: $uploads ),
			// mu-plugins.
			basename( path: WPMU_PLUGIN_DIR ),
		];
		$wp_content_subdirectories = $this->_get_subdirectory_disk_usage(
			directory: WP_CONTENT_DIR,
		);
		$subdirectories_array = [];
		// reformat array from "0 => 3M\t/path/to/dir" to "/path/to/dir => 3M".
		array_map(
			function ( $subdirectory_entry ) use ( &$subdirectories_array ) {
				list( $size, $directory ) = explode(
					separator: "\t",
					string: $subdirectory_entry,
				);
				$subdirectories_array[$directory] = trim( $size );
			},
			$wp_content_subdirectories,
		);
		// filter out non-directories.
		$subdirectories_array = array_filter(
			array: $subdirectories_array,
			callback: function ( $size, $maybe_subdirectory ){
				return is_dir( filename: $maybe_subdirectory );
			},
			mode: ARRAY_FILTER_USE_BOTH,
		);
		// filter out empty directories.
		$subdirectories_array = array_filter(
			array: $subdirectories_array,
			callback: function ( $size, $subdirectory ) {
				return $size != '0'; // zero bytes.
			},
			mode: ARRAY_FILTER_USE_BOTH,
		);
		// if "--sort-by=size," sort the array in descending order based on the key's value.
		if ( $sort_by == 'size' ) {
			arsort(
				array: $subdirectories_array,
				flags: SORT_NUMERIC,
			);
		}
		// find core directories.
		$core_directories = array_filter(
			array: $subdirectories_array,
			callback: function( $size, $subdirectory ) use ( $core_directories_default ) {
				return in_array( needle: basename( path: $subdirectory ), haystack: $core_directories_default );
			},
			mode: ARRAY_FILTER_USE_BOTH,
		);
		// find non-core directories.
		$other_directories = array_filter(
			array: $subdirectories_array,
			callback: function( $size, $subdirectory ) use ( $core_directories_default ) {
				return ! in_array( needle: basename( path: $subdirectory ), haystack: $core_directories_default );
			},
			mode: ARRAY_FILTER_USE_BOTH,
		);
		$core_directories_formatted = array_map(
			function ( $directory, $size ) use ( $os, $core_label ) {
				return [
					$core_label => basename(
						path: $directory,
					),
					'Size' => $this->_reformat_size_format(
						size: $size,
						decimals: 1,
						os: $os,
					),
				];
			},
			array_keys( array: $core_directories ),
			array_values( array: $core_directories ),
		);
		if ( $core_directories_formatted ) {
			$totals[] = [
				$total_label => 'Core directories subtotal',
				'Size' => $this->_reformat_size_format(
					size: array_sum(
						array: $core_directories,
					),
					decimals: 1,
					os: $os,
				),
			];
			WP_CLI\Utils\format_items(
				format: 'table',
				items: $core_directories_formatted,
				fields: array_keys( array: $core_directories_formatted[0] ),
			);
		} else {
			WP_CLI::error(
				message: 'The "du" command was not found or could not be run.',
			);
		}
		$other_directories_formatted = array_map(
			function ( $directory, $size ) use ( $os, $other_label ) {
				return [
					$other_label => basename(
						path: $directory,
					),
					'Size' => $this->_reformat_size_format(
						size: $size,
						decimals: 1,
						os: $os,
					),
				];
			},
			array_keys( array: $other_directories ),
			array_values( array: $other_directories ),
		);
		if ( $other_directories_formatted ) {
			$totals[] = [
				$total_label => 'Other directories subtotal',
				'Size' => $this->_reformat_size_format(
					size: array_sum(
						array: $other_directories,
					),
					decimals: 1,
					os: $os,
				),
			];
			WP_CLI\Utils\format_items(
				format: 'table',
				items: $other_directories_formatted,
				fields: array_keys( array: $other_directories_formatted[0] ),
			);
		}
		// sum the total.
		$totals[] = [
			$total_label => sprintf('Sum total in "%1$s"',
				basename( path: WP_CONTENT_DIR ),
			),
			'Size' => $this->_reformat_size_format(
				size: array_sum(
					array: $subdirectories_array,
				),
				decimals: 1,
				os: $os,
			),
		];
		WP_CLI\Utils\format_items(
			format: 'table',
			items: $totals,
			fields: array_keys( array: $totals[0] ),
		);
	}

	/**
	 * Create a MySQL dump of the database.
	 *
	 * @param string $file
	 * @param array $override_options
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function backup_database(
		string $file = '',
		array $override_options = [],
	):void
	{
		$runcommand_options_defaults = [
			'return' => 'all', // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => false, // halt script execution on error.
		];
		$runcommand_options = wp_parse_args (
			args: $override_options,
			defaults: $runcommand_options_defaults,
		);
		if ( ! $this->dry_run ) {
			if ( ! $this->porcelain ) {
				WP_CLI::log(
					message: 'Backing up the database...',
				);
			}
			$output = WP_CLI::runcommand(
				command: sprintf( 'db export %1$s --porcelain',
					$file,
				),
				options: $runcommand_options,
			);
			if ( $output->return_code == '1' ) {
				if ( $output->stdout ) {
					WP_CLI::error(
						message: $output->stdout,
						exit: false,
					);
				}
				if ( $output->stderr ) {
					WP_CLI::error(
						message: $output->stderr,
						exit: false,
					);
				}
				WP_CLI::halt(
					return_code: 1,
				);
			}
			if ( ! $this->porcelain ) {
				WP_CLI::log(
					message: sprintf( 'Backup filename: %1$s',
						$output->stdout,
					),
				);
				WP_CLI::log(
					message: '...done',
				);
			}
			if ( $this->porcelain ) {
				WP_CLI::log(
					message: $output->stdout,
				);
			}
		}
		else {
			WP_CLI::log(
				message: '...skipping backup on dry-run...',
			);
		}
	}

	/**
	 * Check whether a WP CLI command is available.
	 *
	 * @param string $command_name
	 * @param array $runcommand_options
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function has_command (
		string $command_name,
		array $runcommand_options,
	):void
	{
		// check that the two required plugins are present.
		$return_code = WP_CLI::runcommand(
			command: sprintf( 'cli has-command %1$s',
				$command_name,
			),
			options: $runcommand_options,
		);
		WP_CLI::debug(
			message: sprintf( '$return_code: %1$s',
				$return_code,
			),
		);
		$has_command = ! ( $return_code == '1' );
		WP_CLI::debug(
			message: sprintf( '$has_command: %1$s',
				$has_command ? 'true' : 'false',
			),
		);
		if ( ! $has_command ) {
			WP_CLI::error(
				message: sprintf( 'the "%1$s" command could not be found.',
					$command_name,
				),
			);
		}
	}

	/**
	 * Activate the plugins.
	 *
	 * @param array $ai1wm_plugins
	 * @param array $runcommand_option_defaults
	 * @param string $network_flag
	 *
	 * @return object
	 * @throws ExitException
	 */
	private function activate_ai1wm_plugins(
		array $ai1wm_plugins,
		array $runcommand_option_defaults,
		string $network_flag = '',
	):object
	{
		WP_CLI::log(
			message: sprintf( 'Activating plugins %1$s...',
				implode(
					separator: ' and ',
					array: $ai1wm_plugins,
				),
			)
		);
		$plugin_activate_options = wp_parse_args(
			args: [
				'return' => 'all',
				'exit_error' => false, // don't exit on error.
			],
			defaults: $runcommand_option_defaults,
		);
		$plugin_activate_message = WP_CLI::runcommand(
			command: sprintf( 'plugin activate %1$s %2$s',
				implode(
					separator: ' ',
					array: $ai1wm_plugins,
				),
				$network_flag,
			),
			options: $plugin_activate_options,
		);
		if ( $plugin_activate_message->return_code == '1' ) {
			if ( $plugin_activate_message->stdout ) {
				WP_CLI::error(
					message: $plugin_activate_message->stdout,
					exit: false,
				);
			}
			if ( $plugin_activate_message->stderr ) {
				WP_CLI::log(
					message: $plugin_activate_message->stderr,
				);
			}
			WP_CLI::halt(
				return_code: 1,
			);
		}
		return $plugin_activate_message;
	}

	/**
	 * Deactivate the plugins.
	 *
	 * @param array $ai1wm_plugins
	 * @param array $runcommand_option_defaults
	 * @param string $network_flag
	 *
	 * @return object
	 * @throws ExitException
	 */
	private function deactivate_ai1wm_plugins(
		array $ai1wm_plugins,
		array $runcommand_option_defaults,
		string $network_flag = '',
	):object
	{
		WP_CLI::log(
			message: sprintf( 'Deactivating plugins %1$s...',
				implode(
					separator: ' ',
					array: $ai1wm_plugins,
				),
			)
		);
		$plugin_deactivate_options = wp_parse_args(
			args: [
				'return' => 'all',
				'exit_error' => false, // don't exit on error.
			],
			defaults: $runcommand_option_defaults,
		);
		$plugin_deactivate_message = WP_CLI::runcommand(
			command: sprintf( 'plugin deactivate %1$s %2$s',
				implode(
					separator: ' ',
					array: $ai1wm_plugins,
				),
				$network_flag,
			),
			options: $plugin_deactivate_options,
		);
		if ( $plugin_deactivate_message->return_code == '1' ) {
			if ( $plugin_deactivate_message->stdout ) {
				WP_CLI::error(
					message: $plugin_deactivate_message->stdout,
					exit: false,
				);
			}
			if ( $plugin_deactivate_message->stderr ) {
				WP_CLI::log(
					message: $plugin_deactivate_message->stderr,
				);
			}
			WP_CLI::halt(
				return_code: 1,
			);
		}
		return $plugin_deactivate_message;
	}

	/**
	 * Check whether plugins are installed.
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function plugin_presence_check():void
	{
		// the array below should be empty if the required plugins are present, even if inactive.
		$plugin_presence_check = array_diff(
			$this->get_plugin_dirs(),
			array_map(
				// strip off filename from key ("directory/filename.php").
				fn ( $plugin_slug ) => dirname( $plugin_slug ),
				array_keys( get_plugins() ),
			),
		);
		if ( $plugin_presence_check ) {
			WP_CLI::error(
				message: sprintf( 'the %1$s %2$s %3$s not installed.',
					// list of plugins, separated by " and ".
					implode(
						separator: ' and ',
						array: $plugin_presence_check,
					),
					// conditional plural.
					WP_CLI\Utils\pluralize(
						noun: 'plugin',
						count: count( $plugin_presence_check ),
					),
					// single or plural verb.
					count( $plugin_presence_check ) == 1 ? 'is': 'are',
				),
			);
		}
	}

	/**
	 * Convert the full plugin slugs to just directory names suitable for WP CLI command usage.
	 *
	 * @return array
	 */
	private function get_plugin_dirs():array
	{
		return array_map (
			// strip off filename from key ("directory/filename.php").
			callback: fn ( $plugin_slug ) => dirname( $plugin_slug ),
			array: $this->ai1wm_plugin_slugs,
		);
	}

	/**
	 * Set constant value in wp-config.php.
	 *
	 * @param string $constant
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function set_config_file_value(
		string $constant,
	):void
	{
		// options for WP_CLI::runcommand().
		$runcommand_options = [
			'return'     => 'all',  // capture and return output.
			'launch'     => false, // reuse the current process.
			'exit_error' => false, // halt script execution on error.
		];
		// get current setting.
		$current_value = WP_CLI::runcommand(
			command: sprintf( 'config get %1$s',
				$constant,
			),
			options: $runcommand_options,
		);
		// something went wrong.
		if ( $current_value->return_code == '1' ) {
			WP_CLI::error(
				message: sprintf( 'the value of %1$s could not be obtained.',
					$constant,
				),
				exit: false,
			);
			if ( $current_value->stdout ) {
				WP_CLI::error(
					message: $current_value->stdout,
					exit: false,
				);
			}
			if ( $current_value->stderr ) {
				WP_CLI::log(
					message: $current_value->stderr,
				);
			}
			WP_CLI::halt(
				return_code: 1,
			);
		}
		// set to opposite of current setting.
		$new_value_message = WP_CLI::runcommand(
			command: sprintf( 'config set %1$s %2$s --raw',
				$constant,
				! ! $current_value->stdout ? 'false' : 'true',
			),
			options: $runcommand_options,
		);
		if ( $new_value_message->return_code == '0' ) {
			WP_CLI::success(
				message: sprintf( '%1$s is set to %2$s.',
					$constant,
					! $current_value->stdout ? 'true' : 'false',
				),
			);
		} else {
			WP_CLI::error(
				message: sprintf( '%1$s could not be updated.',
					$constant,
				),
				exit: false,
			);
			if ( $new_value_message->stdout ) {
				WP_CLI::error(
					message: $new_value_message->stdout,
					exit: false,
				);
			}
			if ( $new_value_message->stderr ) {
				WP_CLI::log(
					message: $new_value_message->stderr,
				);
			}
			WP_CLI::halt(
				return_code: 1,
			);
		}
	}

	/**
	 * Get the disk usage of the subdirectories of a given directory.
	 *
	 * @param string $directory
	 *
	 * @return array
	 */
	private function _get_subdirectory_disk_usage(
		string $directory,
	):array
	{
		$command = 'du';
		$arguments = [
			's', // summary.
		];
		if ( file_exists( filename: $directory ) ) {
			$exec = exec(
				command: sprintf('%1$s -%2$s %3$s/*',
					$command,
					implode( $arguments ),
					$directory,
				),
				output: $output,
			);
			// no output, or the "du" command is not found.
			if ( empty( $exec )) {
				return [];
			}
			return $output;
		}
		return [];
	}

	/**
	 * Reformat the return value of size_format(). Example: "5 MB" => "5M".
	 *
	 * @param string $size
	 * @param int $decimals
	 * @param string $os
	 *
	 * @return string
	 */
	private function _reformat_size_format(
		string $size,
		int $decimals = 0,
		string $os = '',
	):string
	{
		// we can't get the directory in bytes on macOS. see the "du" man page.
		$du_multiplier = 1024;
		if ( $os == 'Darwin' ) {
			$du_multiplier = 512;
		}
		list ( $amount, $unit ) = explode(
			separator: ' ',
			string: size_format(
				bytes: $size * $du_multiplier,
				decimals: $decimals,
			)
		);
		return sprintf( '%1$s%2$s',
			$amount,
			$unit[0], // get first character.
		);
	}

}
