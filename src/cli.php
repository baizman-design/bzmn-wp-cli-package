<?php

namespace baizman_design_cli;

use WP_CLI;
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
	 * Print the current environment.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn print-wp-environment
	 *
	 * @subcommand print-wp-environment
	 */
	public function print_wp_environment():void
	{
		WP_CLI::log( sprintf( 'environment: %1$s',
			wp_get_environment_type(),
		));
	}


    /**
     * Print image sizes.
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
	 * [--include_builtins]
	 * : Include built-in post types.
	 *
	 * ## EXAMPLES
	 *
	 * wp bzmn print-custom-post-types
	 *
	 * @subcommand print-custom-post-types
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
		$args = [];
		if ( ! $include_builtins ) {
			$args = [
				'_builtin' => false,
			];
		}
		$custom_post_type_objects = get_post_types(
			args: $args,
			output: 'objects',
		);
		$post_types = [];
		// this structure simplifies the format of the array elements.
		array_map(
			callback: function( $custom_post_type ) use ( & $post_types ){
				$post_types[] = [
					'name' => $custom_post_type->label,
					'slug' => $custom_post_type->name,
					'public?' => $custom_post_type->public ? 'X': '',
					'custom?' => $custom_post_type->_builtin ? '': 'X',
				];
			},
			array: $custom_post_type_objects,
		);
		// sort $post_types by $sort_by.
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
	 * Back up the database.
	 *
	 * @return void
	 */
	private function backup_database ():void
	{
		$command_options = [
			'return'     => true,   // Capture and return output
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		];
		if ( ! $this->dry_run ) {
			WP_CLI::log( message: 'backing up the database...' );
			$output = WP_CLI::runcommand(
				command: 'db export --porcelain',
				options: $command_options,
			);
			WP_CLI::log( sprintf( 'backup filename: %1$s',
				$output,
			));
			WP_CLI::log( message: '...done' );
		}
		else {
			WP_CLI::log( message: '...skipping backup on dry-run...' );
		}
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
