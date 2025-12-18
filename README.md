# bzmn wp cli package

A WP CLI package with commands for multiple clients.

## installation

Modify the repositories in `~/.wp-cli/packages/composer.json`:

```json
    "repositories": [
    {
        "type": "composer",
        "url": "https://wp-cli.org/package-index/"
    },
    {
        "type": "path",
        "url": "/Users/saul/Documents/bzmn-wp-cli-package"
    }
],
```

Then run the commands below:

```shell
cd ~/.wp-cli/packages
composer require "baizman-design/bzmn-wp-cli-package @dev"
```

## upgrading

To upgrade the package:

```shell
composer update baizman-design/bzmn-wp-cli-package
```
