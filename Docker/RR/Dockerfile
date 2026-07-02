FROM ghcr.io/roadrunner-server/roadrunner:2025.1.0 AS roadrunner
FROM php:8.5-cli

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN apt-get update && apt-get install -y \
    curl \
    $PHPIZE_DEPS \
    librabbitmq-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://pecl.php.net/get/amqp-2.2.0.tgz -o /tmp/amqp.tgz \
    || curl -fsSL http://pecl.php.net/get/amqp-2.2.0.tgz -o /tmp/amqp.tgz \
    && pecl install /tmp/amqp.tgz \
    && docker-php-ext-enable amqp \
    && rm -f /tmp/amqp.tgz

RUN docker-php-ext-install sockets pdo_pgsql

# Рабочая директория
WORKDIR /var/www/html

# Запускаем RoadRunner
CMD ["rr", "serve", "-c", ".rr.yaml"]