<?php

namespace baizman_design_cli;

use WP_CLI;
use WP_CLI\ExitException;
use WP_Query;

class cli {

	// flag to set whether the changes are made.
	private bool $dry_run = false;

	/**
	 * Flush rewrite rules.
	 *
	 * ## OPTIONS
	 *
	 * [--hard]
	 * : Hard flush. Updates .htaccess.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn flush-rewrite-rules
	 * wp bzmn flush-rewrite-rules --hard
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
		$defaults = [
			'hard' => false,
		];
		$assoc_args = wp_parse_args(
			args: $assoc_args,
			defaults: $defaults,
		);
		extract( $assoc_args );
		flush_rewrite_rules( hard: $hard );
		WP_CLI::success ( sprintf( 'The rewrite rules have been %1$s-flushed.',
			$hard ? 'hard' : 'soft',
		));
	}

	/**
	 * Print the environment type.
	 *
	 * Displays the value of the WP_ENVIRONMENT_TYPE constant.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn print-wp-environment
	 *
	 * @subcommand print-wp-environment
	 * @alias print_wp_environment
	 */
	public function print_wp_environment():void
	{
		WP_CLI::log( sprintf( 'environment: %1$s',
			wp_get_environment_type(),
		));
	}

    /**
     * Print the image sizes registered via add_image_size().
	 *
     * [--fields=<fields>]
	 * : Output specific fields. The default is all fields.
	 * ---
	 * options:
	 *   - size
	 *   - width
	 *   - height
	 *   - crop
     *
     * [--format=<format>]
     * : Format output.
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - yaml
     *
     * ## EXAMPLES
     *
     * wp bzmn print-image-sizes
     * wp bzmn print-image-sizes --format=table
     * wp bzmn print-image-sizes --fields=size,crop
     * wp bzmn print-image-sizes --fields=size,crop --format=csv
     *
     * @subcommand print-image-sizes
     * @alias print_image_sizes
     */
    public function print_image_sizes(
		array $args = [],
		array $assoc_args = [],
    ): void
    {
		$default_fields = [
			'size',
			'width',
			'height',
			'crop',
		];
        $defaults = [
            'format' => 'table',
			'fields' => implode(
				separator: ',',
				array: $default_fields,
			),
		];
        $assoc_args = wp_parse_args(
            args: $assoc_args,
            defaults: $defaults
        );
        extract( $assoc_args );
		$requested_fields = explode(
			separator: ',',
			string: $fields,
		);
        // https://developer.wordpress.org/reference/functions/wp_get_registered_image_subsizes/
		$registered_image_subsizes = wp_get_registered_image_subsizes();
        if ( $registered_image_subsizes ) {
            $items = [];
            foreach ( $registered_image_subsizes as $size => $details ) {
				$width = $details['width'];
				$height = $details['height'];
				$crop = $details['crop'] ? 'Y' : 'N';
                $items[] = compact( $requested_fields );
            }
			if ( $items ) {
	            WP_CLI\Utils\format_items(
	                format: $format,
	                items: $items,
	                fields: array_keys($items[0]),
	            );
			}
        } else {
            WP_CLI::warning(
                message: 'No image sizes were detected.',
            );
        }
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
	 * [--sort_by=<field>]
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
	 * wp bzmn print-custom-post-types
	 * wp bzmn print-custom-post-types --sort_by=slug
	 * wp bzmn print-custom-post-types --include_builtins
	 * wp bzmn print-custom-post-types --include_builtins --sort_by=slug
	 *
	 * @subcommand print-custom-post-types
	 * @alias print_custom_post_types
	 */
	public function print_custom_post_types (
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$defaults = [
			'format' => 'table',
			'include_builtins' => false,
			'sort_by' => 'name', // post type label. corresponds to custom array in $post_types.
		];
		$assoc_args = wp_parse_args (
			args: $assoc_args,
			defaults: $defaults,
		);
		extract( $assoc_args );
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
	 * wp bzmn set-toolset-post-type-pagination user@domain.com 25
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
				WP_CLI::error( sprintf( 'The user "%1$s" is not a valid user.',
					$email,
				));
			}
		} else {
			WP_CLI::error( 'The arguments were not valid.' );
		}
	}

    /**
     * Delete WooCommerce payment field style transients.
     *
     * ## EXAMPLES
     *
     * wp bzmn delete-wc-transients
     *
     * @subcommand delete-wc-transients
     * @alias delete_wc_transients
     */
    public function delete_wc_transients():void
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
        WP_CLI::success( 'Deleted WooCommerce payment field style transients.' );
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
	 * wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page
	 * wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page --dry_run
	 *
	 * @subcommand populate-post-meta
	 * @alias populate_post_meta
	 */
	public function populate_post_meta(
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$defaults = [
			'dry_run' => false,
		];
		$assoc_args = wp_parse_args(
			args: $assoc_args,
			defaults: $defaults,
		);
		extract( $assoc_args );
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
			foreach ($posts->posts as $post_id) {
				WP_CLI::log(sprintf('Updating post ID %1$d (%2$d/%3$d)...',
					$post_id,
					$post_counter,
					$post_count,
				));
				// update the post meta data.
				if ( ! $this->dry_run ) {
					update_post_meta( post_id: $post_id, meta_key: $field, meta_value: $value );
				}
				$post_counter++;
			}
			if ( ! $this->dry_run ) {
				WP_CLI::success( sprintf( '%1$d posts were updated.',
					$post_counter-1,
				) );
			} else {
				WP_CLI::log( sprintf( '%1$d posts were not updated.',
					$post_counter-1,
				) );
			}
		} else {
			// zero posts found.
			WP_CLI::error(sprintf('There were no posts for the post type "%1$s."',
				$post_type,
			));
		}
	}

	/**
	 * Toggle the boolean value of the WP_DEBUG constant in wp-config.php.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn toggle-debug
	 *
	 * @when before_wp_load
	 * @subcommand toggle-debug
	 * @alias toggle_debug
	 */
	public function toggle_debug():void {
		$constant = 'WP_DEBUG';
		$command_options = [
			'return' => true,  // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
		];
		// get current setting.
		$current_value = WP_CLI::runcommand(
			command: sprintf( 'config get %1$s',
				$constant,
			),
			options: $command_options,
		);
		// set to opposite of current setting.
		$new_value_message = WP_CLI::runcommand(
			command: sprintf( 'config set %1$s %2$s --raw',
				$constant,
				! ! $current_value ? 'false' : 'true',
			),
			options: $command_options,
		);
		if ( str_contains( haystack: $new_value_message, needle: 'Success:' ) ) {
			WP_CLI::success( sprintf( '%1$s is set to %2$s.',
				$constant,
				! $current_value ? 'true' : 'false',
			));
		} else {
			WP_CLI::error( sprintf( '%1$s could not be updated.',
				$constant,
			));
		}
	}

	/**
	 * Toggle the boolean value of the WP_DEBUG_DISPLAY constant in wp-config.php.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn toggle-debug
	 *
	 * @when before_wp_load
	 * @subcommand toggle-debug-display
	 * @alias toggle_debug_display
	 */
	public function toggle_debug_display():void {
		$constant = 'WP_DEBUG_DISPLAY';
		$command_options = [
			'return' => true,  // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
		];
		// get current setting.
		$current_value = WP_CLI::runcommand(
			command: sprintf( 'config get %1$s',
				$constant,
			),
			options: $command_options,
		);
		// set to opposite of current setting.
		$new_value_message = WP_CLI::runcommand(
			command: sprintf( 'config set %1$s %2$s --raw',
				$constant,
				! ! $current_value ? 'false' : 'true',
			),
			options: $command_options,
		);
		if ( str_contains( haystack: $new_value_message, needle: 'Success:' ) ) {
			WP_CLI::success( sprintf( '%1$s is set to %2$s.',
				$constant,
				! $current_value ? 'true' : 'false',
			));
		} else {
			WP_CLI::error( sprintf( '%1$s could not be updated.',
				$constant,
			));
		}
	}

	/**
	 * Back up a website using All-in-One WP Migration and Backup plugins.
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
	 * ## EXAMPLES
	 *
	 * wp bzmn backup
	 * wp bzmn backup --type=quick
	 * wp bzmn backup --type=full
	 * wp bzmn backup --type=sql
	 *
     * @subcommand backup
     * @alias back
	 */
	public function backup (
		array $args = [],
		array $assoc_args = [],
	):void
	{
		$ai1wm = 'ai1wm';
		$defaults = [
			'type' => 'quick',
		];
		$assoc_args = wp_parse_args (
			args: $assoc_args,
			defaults: $defaults,
		);
		extract( $assoc_args );
		// mysql dump.
		if ( $type == 'sql' ) {
			$this->backup_database( file: sprintf('%1$s/%2$s-%3$s-export.sql',
				untrailingslashit( ABSPATH ),
				DB_NAME,
				date( 'YmdHis' ),
			));
			WP_CLI::success( sprintf('%1$s backup succeeded.',
				ucfirst( $type ),
			));
			// we're done here.
			WP_CLI::halt( return_code: 0 );
		}
		// plugins to activate / deactivate.
		$ai1wm_plugins = [
			'all-in-one-wp-migration',
			'all-in-one-wp-migration-multisite-extension',
		];
		// arguments for quick backup, sans "--".
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
			callback: fn ( $arg ) => '--' . $arg,
			array: $quick_backup_args,
		);
		// default parameters to WP_CLI::runcommand().
		$runcommand_option_defaults = [
			'return' => true,  // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
		];
		// is this a multisite installation? if so, add flag for activate / deactivate commands.
		$network_flag = is_multisite() ? '--network' : '';
		$has_command_return_options = wp_parse_args (
			args: [
				'return' => 'return_code', // only return status code (0 for yes or 1 for no).
				'exit_error' => false, // don't exit on error.
			],
			defaults: $runcommand_option_defaults,
		);
		$has_ai1wm_command = $this->has_ai1wm_plugins(
			command_name: $ai1wm,
			runcommand_options: $has_command_return_options,
		);
		WP_CLI::debug( sprintf( '$has_ai1wm_command: %1$s',
			$has_ai1wm_command ? 'true' : 'false',
		));
		// if the command was not present, activate the necessary plugins.
		if ( ! $has_ai1wm_command ) {
			WP_CLI::log( sprintf( 'Activating plugins %1$s...',
				implode( separator: ' ', array: $ai1wm_plugins ),
			));
			// TODO: check return status of this command.
			// if it fails, error() out.
			$plugin_activate_options = wp_parse_args (
				args: [
					'return' => 'all', // only return status code (0 for yes or 1 for no).
					'exit_error' => false, // don't exit on error.
				],
				defaults: $runcommand_option_defaults,
			);
			$plugin_activate_message = WP_CLI::runcommand(
				command: sprintf( 'plugin activate %1$s %2$s',
					implode( separator: ' ', array: $ai1wm_plugins ),
					$network_flag,
				),
				options: $plugin_activate_options,
			);
			if ( $plugin_activate_message->return_code == '1' ) {
				WP_CLI::log( $plugin_activate_message->stdout );
				WP_CLI::log( $plugin_activate_message->stderr );
				WP_CLI::halt( return_code: 1 );
			}
			$has_command_return_options['launch'] = true;
			$has_ai1wm_command = $this->has_ai1wm_plugins(
				command_name:$ai1wm,
				runcommand_options: $has_command_return_options,
			);
			WP_CLI::debug( sprintf( '$has_ai1wm_command: %1$s',
				$has_ai1wm_command ? 'true' : 'false',
			));
			if ( ! $has_ai1wm_command ) {
				WP_CLI::error(
					message: sprintf( 'the %s plugins could not be activated.',
						implode( separator: ' ', array: $ai1wm_plugins ),
					),
					exit: true,
				);
			}
			WP_CLI::log( sprintf( '%1$s',
				$plugin_activate_message->stdout,
			));
		} else {
			WP_CLI::log( sprintf( 'Plugins %1$s are already activated. Continuing...',
				implode( separator: ' and ', array: $ai1wm_plugins ),
			));
		}
		$ai1wm_command_arguments = '';
		if ( $type == 'quick' ) {
			$ai1wm_command_arguments = implode( separator: ' ', array: $quick_backup_args );
		}
		WP_CLI::log( sprintf( 'Backup type: %1$s',
			$type,
		));
		WP_CLI::log( sprintf( 'Backup site: %1$s...',
			get_site_url(),
		));
		$backup_command_return_options = wp_parse_args (
			args: [
				'launch' => true, // run in new process because we've modified the WordPress environment when we activated plugins.
			],
			defaults: $runcommand_option_defaults,
		);
		$return_message = WP_CLI::runcommand(
			command: sprintf( '%1$s backup %2$s',
				$ai1wm,
				$ai1wm_command_arguments,
			),
			options: $backup_command_return_options,
		);
		WP_CLI::log( sprintf( '%1$s',
			$return_message,
		));
		WP_CLI::log( sprintf( 'Deactivating plugins %1$s...',
			implode( separator: ' ', array: $ai1wm_plugins ),
		));
		// always deactivate the plugins. (but maybe not if they were already active?)
		$return_message = WP_CLI::runcommand(
			command: sprintf( 'plugin deactivate %1$s %2$s',
				implode( separator: ' ', array: $ai1wm_plugins ),
				$network_flag,
			),
			options: $runcommand_option_defaults,
		);
		WP_CLI::log( sprintf( '%1$s',
			$return_message,
		));
		WP_CLI::success( sprintf( '%1$s backup succeeded.',
			ucfirst( $type ),
		));
	}

	/**
	 * Create a MySQL dump of the database.
	 *
	 * @param string $file
	 *
	 * @return void
	 * @throws ExitException
	 */
	private function backup_database(
		string $file = '',
	):void
	{
		$runcommand_options = [
			'return' => 'all', // capture and return output.
			'launch' => false, // reuse the current process.
			'exit_error' => true, // halt script execution on error.
		];
		if ( ! $this->dry_run ) {
			WP_CLI::log( message: 'backing up the database...' );
			$output = WP_CLI::runcommand(
				command: sprintf( 'db export %1$s --porcelain',
					$file,
				),
				options: $runcommand_options,
			);
			if ( $output->return_code == '1' ) {
				WP_CLI::error(
					message: $output->stderr,
					exit: true,
				);
			}
			WP_CLI::log( sprintf( 'backup filename: %1$s',
				$output->stdout,
			));
			WP_CLI::log( message: '...done' );
		}
		else {
			WP_CLI::log( message: '...skipping backup on dry-run...' );
		}
	}

	/**
	 * @param string $command_name
	 * @param array $runcommand_options
	 *
	 * @return bool
	 */
	private function has_ai1wm_plugins (
		string $command_name,
		array $runcommand_options,
	):bool
	{
		// check that the two required plugins are present.
		$return_code = WP_CLI::runcommand(
			command: sprintf( 'cli has-command %1$s',
				$command_name,
			),
			options: $runcommand_options,
		);
		WP_CLI::debug( sprintf( '$return_code: %1$s',
			$return_code,
		));
		return ! ( $return_code == '1' );
	}

	/**
	 * Pluralize a word, if needed.
	 *
	 * @param array $count
	 * @param string $plural_suffix
	 *
	 * @return string
	 */
	private function pluralize (
		array $count,
		string $plural_suffix = 's',
	):string
	{
		return count( $count ) > 1 ? $plural_suffix : '';
	}

}
