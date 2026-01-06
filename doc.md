baizman-design/bzmn-wp-cli-package
==================================

A WP CLI package with commands for multiple clients.



Quick links: [Using](#using)

## Using

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

wp bzmn backup
wp bzmn backup --type=quick
wp bzmn backup --type=full
wp bzmn backup --type=sql



### wp bzmn clear-cloudflare-cache

Clear the Cloudflare cache. Default: purge everything.

~~~
wp bzmn clear-cloudflare-cache <api_key> <zone_id>
~~~

API documentation: https://developers.cloudflare.com/api/resources/cache/

**OPTIONS**

	<api_key>
		Cloudflare API key.

	<zone_id>
		Cloudflare zone ID.

**EXAMPLES**

wp bzmn clear-cloudflare-cache 12345 ABCDE



### wp bzmn clear-sucuri-cache

Clear the Sucuri firewall cache.

~~~
wp bzmn clear-sucuri-cache <api_key> <api_secret> [<filename>]
~~~

**OPTIONS**

	<api_key>
		Sucuri API key.

	<api_secret>
		Sucuri API secret.

	[<filename>]
		A filename to remove from the cache.

**EXAMPLES**

wp bzmn clear-sucuri-cache 12345 ABCDE
wp bzmn clear-sucuri-cache 12345 ABCDE index.php



### wp bzmn delete-wc-transients

Delete WooCommerce payment field style transients.

~~~
wp bzmn delete-wc-transients 
~~~

**EXAMPLES**

wp bzmn delete-wc-transients



### wp bzmn flush-rewrite-rules

Flush rewrite rules.

~~~
wp bzmn flush-rewrite-rules [--hard]
~~~

**OPTIONS**

	[--hard]
		Hard flush. Updates .htaccess.

**EXAMPLES**

wp bzmn flush-rewrite-rules
wp bzmn flush-rewrite-rules --hard



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

wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page
wp bzmn populate-post-meta wpcf-hide-page-in-navigation 0 page --dry_run



### wp bzmn print-custom-post-types

Print custom post types.

~~~
wp bzmn print-custom-post-types [--format=<format>] [--sort_by=<field>] [--include_builtins]
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

	[--sort_by=<field>]
		Sort results. Options: name, slug. Default: name.
		---
		default: name
		options:
		  - name
		  - slug

	[--include_builtins]
		Include built-in post types.

**EXAMPLES**

wp bzmn print-custom-post-types
wp bzmn print-custom-post-types --sort_by=slug
wp bzmn print-custom-post-types --include_builtins
wp bzmn print-custom-post-types --include_builtins --sort_by=slug



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

wp bzmn print-image-sizes
wp bzmn print-image-sizes --format=table
wp bzmn print-image-sizes --fields=size,crop
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

wp bzmn print-wp-environment
wp bzmn print-wp-environment --porcelain



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

wp bzmn set-toolset-post-type-pagination user@domain.com 25



### wp bzmn toggle-debug

Toggle the boolean value of the WP_DEBUG constant in wp-config.php.

~~~
wp bzmn toggle-debug 
~~~

**EXAMPLES**

wp bzmn toggle-debug



### wp bzmn toggle-debug-display

Toggle the boolean value of the WP_DEBUG_DISPLAY constant in wp-config.php.

~~~
wp bzmn toggle-debug-display 
~~~

**EXAMPLES**

wp bzmn toggle-debug-display


