ARG PHP_VERSION=8.5.12
FROM php:${PHP_VERSION}-cli-trixie

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/development.ini

RUN useradd --create-home --shell /bin/bash vscode \
    && mkdir -p /workspaces/sql-injection-demo \
    && chown vscode:vscode /workspaces/sql-injection-demo

USER vscode
WORKDIR /workspaces/sql-injection-demo

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/workspaces/sql-injection-demo"]
