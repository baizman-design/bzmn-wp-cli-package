# bzmn wp cli package

A WP CLI package with commands for multiple clients. See [`doc.md`](doc.md) for command documentation.

## installation

### remote (via github)

```shell
wp package browse
cd ~/.wp-cli/packages
composer config repositories.baizman-design vcs https://github.com/baizman-design/bzmn-wp-cli-package
composer require "baizman-design/bzmn-wp-cli-package @dev"
```

### local (via directory)

```shell
wp package browse
cd ~/.wp-cli/packages
composer config repositories.baizman-design path /path/to/bzmn-wp-cli-package
composer require "baizman-design/bzmn-wp-cli-package @dev"
```

## upgrading

To upgrade the package:

```shell
composer update baizman-design/bzmn-wp-cli-package
```
