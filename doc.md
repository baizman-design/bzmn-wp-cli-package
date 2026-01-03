baizman-design/bzmn-wp-cli-package
==================================

A WP CLI package with commands for multiple clients.



Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Support](#support)

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

## Installing

Installing this package requires WP-CLI v1.3.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install the latest stable version of this package with:

```bash
wp package install baizman-design/bzmn-wp-cli-package:@stable
```

To install the latest development version of this package, use the following command instead:

```bash
wp package install baizman-design/bzmn-wp-cli-package:dev-production
```

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/baizman-design/bzmn-wp-cli-package/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/baizman-design/bzmn-wp-cli-package/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/baizman-design/bzmn-wp-cli-package/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Support

GitHub issues aren't for general support questions, but there are other venues you can try: https://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
