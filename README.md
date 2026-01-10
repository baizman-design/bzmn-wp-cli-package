# bzmn wp cli package

A WP CLI package with commands for multiple clients. See [`doc.md`](doc.md) for command documentation.

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

## github actions

There are a few automated processes.

+ `publish-ghcr-package.yml`. Build and publish the custom Docker image.
+ `regenerate-documentation.yml`. Generate the command documentation and save it as a Markdown file. Uses a custom Docker image (see "[docker](#docker)" section).
+ `update-composer-json-commands.yml`. Updates `composer.json` to automatically add new commands (public methods) added in `src/cli.php` to the `extra > commands` section. (These entries are required by the `wp scaffold package-readme` command.)
+ `update-package.yml`. Update the package on multiple remote hosts. The remote hosts reside in a repository variable, `$REPO_REMOTE_HOSTS`, and correspond to SSH aliases stored in `~/.ssh/config`. This workflow runs manually but could be run automatically.
