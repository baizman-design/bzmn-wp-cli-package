There are a few automated processes.

+ `publish-ghcr-package.yml`. Build and publish the custom Docker image.
+ `regenerate-documentation.yml`. Generate the command documentation and save it as a Markdown file. Uses a custom Docker image (see "[docker](#docker)" section).
+ `sync-composer-json-commands-template.yml`. Automatically syncs new commands (public methods) added in `src/cli.php` to the `extra > commands` section in `composer.json`. (These entries are required by the `wp scaffold package-readme` command.)
+ `update-package.yml`. Update the package on multiple remote hosts. The remote hosts reside in a repository variable, `$REPO_REMOTE_HOSTS`, and correspond to WP CLI aliases stored in `~/.wp-cli/config.yml`. This workflow runs manually but could be run automatically.
