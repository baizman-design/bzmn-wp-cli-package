FROM alpine:3.19

LABEL org.opencontainers.image.source="https://github.com/baizman-design/bzmn-wp-cli-package"
LABEL org.opencontainers.image.description="A custom container to regenerate documentation for a custom WP CLI command."

# download all necessary pieces of software
RUN apk update \
    && apk upgrade \
    && apk add --no-cache php83 php83-phar php83-mbstring php83-openssl php83-curl php83-simplexml php83-dom php83-tokenizer php83-xml php83-xmlwriter openssh git curl bash zip

WORKDIR /usr/local/bin

# wp cli download.
# composer download.
# the packaged version is older than the latest version, so we download and install it manually.
# enable execute permission.
# link php
# install scaffold WP CLI command
RUN wget -q -O wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && wget -q -O composer https://getcomposer.org/download/latest-stable/composer.phar \
    && chmod +x wp composer \
    && ln -s /usr/bin/php83 ./php \
    && wp package install "wp-cli/scaffold-package-command:^2"

# create wp-cli directory.
WORKDIR /root/.wp-cli

WORKDIR /root

ENTRYPOINT ["/bin/sh"]
