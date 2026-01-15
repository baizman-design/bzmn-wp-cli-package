baizman-design/bzmn-wp-cli-package
==================================

A WP CLI package with commands for multiple clients.



Quick links: [installation](#installation) | [docker](#docker) | [automation](#automation) | [using](#using)

## installation

### remote (via github)

```shell
wp package install baizman-design/bzmn-wp-cli-package
```

### local (via directory)

```shell
wp package install /path/to/bzmn-wp-cli-package
```

## upgrading

To upgrade the package:

```shell
wp package update
```

## docker

The `Dockerfile` creates a custom image with the [`wp scaffold package`](https://developer.wordpress.org/cli/commands/scaffold/package/) command pre-installed. The image is used with the `regenerate-documentation.yml` GitHub Action to automatically generate the command documentation. To build the Docker image locally, run `bin/local-dev.sh`.

## automation

There are a few automated processes.

+ `publish-ghcr-package.yml`. Build and publish the custom Docker image.
+ `regenerate-documentation.yml`. Generate the command documentation and save it as a Markdown file. Uses a custom Docker image (see "[docker](#docker)" section).
+ `update-composer-json-commands.yml`. Updates `composer.json` to automatically add new commands (public methods) added in `src/cli.php` to the `extra > commands` section. (These entries are required by the `wp scaffold package-readme` command.)
+ `update-package.yml`. Update the package on multiple remote hosts. The remote hosts reside in a repository variable, `$REPO_REMOTE_HOSTS`, and correspond to SSH aliases stored in `~/.ssh/config`. This workflow runs manually but could be run automatically.

## using

This package implements the following commands:

### wp bzmn backup

Back up a website using All-in-One WP Migration and Backup plugins.

~~~
wp bzmn backup [--type=<type>]
~~~

**OPTIONS**

	[--type=<type>]
		Type of backup. Options: quick, full, sql. Default: quick.
		---
		default: quick
		options:
		  - quick
		  - full
		  - sql

**EXAMPLES**

    # Make a default (quick) backup.
    wp bzmn backup

    # Make a quick (database) backup.
    wp bzmn backup --type=quick

    # Make a full (file and database) backup.
    wp bzmn backup --type=full

    # Make a database (SQL) backup.
    wp bzmn backup --type=sql



### wp bzmn clear-cloudflare-cache

Clear the Cloudflare cache. Default: purge everything. Uses $CLOUDFLARE_API_KEY environment variable.

~~~
wp bzmn clear-cloudflare-cache <zone_id>
~~~

API documentation: https://developers.cloudflare.com/api/resources/cache/

**OPTIONS**

	<zone_id>
		Cloudflare zone ID.

**EXAMPLES**

    # Clear the cloudflare cache for a domain.
    wp bzmn clear-cloudflare-cache 12345
    Success: The Cloudflare cache has been cleared.



### wp bzmn clear-sucuri-cache

Clear the Sucuri firewall cache. Uses $SUCURI_API_KEY and $SUCURI_API_SECRET environment variables.

~~~
wp bzmn clear-sucuri-cache [<filename>]
~~~

**OPTIONS**

	[<filename>]
		A filename to remove from the cache.

**EXAMPLES**

    # Clear the Sucuri cache for a domain.
    wp bzmn clear-sucuri-cache

    # Clear the Sucuri cache for the file index.php.
    wp bzmn clear-sucuri-cache index.php



### wp bzmn delete-woocommerce-transients

Delete WooCommerce payment field style transients.

~~~
wp bzmn delete-woocommerce-transients 
~~~

**EXAMPLES**

    # Delete the WooCommerce transients.
    wp bzmn delete-woocommerce-transients
    Success: Deleted WooCommerce payment field style transients.



### wp bzmn disk-usage

Display the disk usage of WP_CONTENT_DIR.

~~~
wp bzmn disk-usage [--sort-by=<field>]
~~~

**OPTIONS**

	[--sort-by=<field>]
		Sort directories in each subsection alphabetically or by size. Options: name, size. Default: name.
		---
		default: name
		options:
		  - name
		  - size

**EXAMPLES**

    # Display the disk usage.
    wp bzmn disk-usage
    wp bzmn disk-usage --sort-by=size



### wp bzmn flush-rewrite-rules

Flush URL rewrite rules.

~~~
wp bzmn flush-rewrite-rules [--hard]
~~~

**OPTIONS**

	[--hard]
		Hard flush. Updates .htaccess.

**EXAMPLES**

    # Soft-flush the rewrite rules.
    $ wp bzmn flush-rewrite-rules
    Success: The rewrite rules have been soft-flushed.

    # Hard-flush the rewrite rules.
    $ wp bzmn flush-rewrite-rules --hard
    Success: The rewrite rules have been hard-flushed.



### wp bzmn populate-post-meta

Update the value of a post meta field for a given post type.

~~~
wp bzmn populate-post-meta <field> <value> <post_type> [--dry_run]
~~~

If the field is not present, the field and value will be added.

**OPTIONS**

	<field>
		The field to update.

	<value>
		The field's new value.

	<post_type>
		The post type.

	[--dry_run]
		Dry run. Do not modify the database.

**EXAMPLES**

    # Set the value of "wpcf-hide-page-in-navigation" to "0" for all pages.
    wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page

    # Set the value of "wpcf-hide-page-in-navigation" to "0" for all pages in a test run.
    wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page --dry_run



### wp bzmn print-custom-post-types

Print custom post types.

~~~
wp bzmn print-custom-post-types [--format=<format>] [--sort-by=<field>] [--include_builtins]
~~~

**OPTIONS**

	[--format=<format>]
		Format result set. Options: table, json, csv, yaml. Default: table.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		  - yaml

	[--sort-by=<field>]
		Sort results. Options: name, slug. Default: name.
		---
		default: name
		options:
		  - name
		  - slug

	[--include_builtins]
		Include built-in post types.

**EXAMPLES**

    # Print the custom post types sorted alphabetically by name.
    wp bzmn print-custom-post-types

    # Print the custom post types sorted alphabetically by slug.
    wp bzmn print-custom-post-types --sort-by=slug

    # Print the custom and built-in post types.
    wp bzmn print-custom-post-types --include_builtins

    # Print the custom and built-in post types sorted alphabetically by slug.
    wp bzmn print-custom-post-types --include_builtins --sort-by=slug



### wp bzmn print-image-sizes

Print the image sizes registered via add_image_size().

~~~
wp bzmn print-image-sizes [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	[--fields=<fields>]
		Output specific fields. The default is all fields.
		---
		options:
		  - size
		  - width
		  - height
		  - crop

	[--format=<format>]
		Format output.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		  - yaml

**EXAMPLES**

    # Print the image sizes in a table.
    wp bzmn print-image-sizes

    # Print the image sizes in as json.
    wp bzmn print-image-sizes --format=json

    # Print the image sizes with only the size and crop columns.
    wp bzmn print-image-sizes --fields=size,crop

    # Print the image sizes with only the size and crop columns in csv format.
    wp bzmn print-image-sizes --fields=size,crop --format=csv



### wp bzmn print-wp-environment

Print the environment type.

~~~
wp bzmn print-wp-environment [--porcelain]
~~~

Displays the value of the WP_ENVIRONMENT_TYPE constant.

**OPTIONS**

	[--porcelain]
		Print just the environment name.

**EXAMPLES**

    # Print the environment type with a prefix.
    $ wp bzmn print-wp-environment
    environment: production

    # Print the environment type without a prefix.
    $ wp bzmn print-wp-environment --porcelain
    production



### wp bzmn restore

Restore WP instance from All-in-One WP Migration and Backup .wpress file.

~~~
wp bzmn restore <wpress_backup_file>
~~~

**OPTIONS**

	<wpress_backup_file>
		.wpress backup file.

**EXAMPLES**

    # Restore website from a1b2c3.wpress.
    wp bzmn restore a1b2c3.wpress



### wp bzmn set-toolset-post-type-pagination

Set pagination on Toolset > Post Types screen.

~~~
wp bzmn set-toolset-post-type-pagination <email> <pagination>
~~~

This is a documented bug:
https://toolset.com/errata/pagination-screen-option-on-post-type-listing-page-doesnt-work/

**OPTIONS**

	<email>
		The user's email.

	<pagination>
		The pagination value.

**EXAMPLES**

    # Set the pagination for user@domain.com to 25 items per page.
    wp bzmn set-toolset-post-type-pagination user@domain.com 25
    Success: The pagination for "user@domain.com" was set to "25."



### wp bzmn toggle-debug

Toggle the boolean value of the WP_DEBUG constant in wp-config.php.

~~~
wp bzmn toggle-debug 
~~~

**EXAMPLES**

    # Toggle the WP_DEBUG constant value.
    wp bzmn toggle-debug
    Success: WP_DEBUG is set to true.

    # Toggle the WP_DEBUG constant value.
    wp bzmn toggle-debug
    Success: WP_DEBUG is set to false.



### wp bzmn toggle-debug-display

Toggle the boolean value of the WP_DEBUG_DISPLAY constant in wp-config.php.

~~~
wp bzmn toggle-debug-display 
~~~

**EXAMPLES**

    # Toggle the WP_DEBUG_DISPLAY constant value.
    wp bzmn toggle-debug-display
    Success: WP_DEBUG_DISPLAY is set to true.

    # Toggle the WP_DEBUG_DISPLAY constant value.
    wp bzmn toggle-debug-display
    Success: WP_DEBUG_DISPLAY is set to false.


