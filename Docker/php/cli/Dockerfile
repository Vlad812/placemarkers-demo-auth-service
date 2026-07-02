FROM php:8.5-cli

# Устанавливаем зависимости для Composer и расширений
RUN apt-get update && apt-get install -y \
    curl \
    git \
    $PHPIZE_DEPS \
    librabbitmq-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://pecl.php.net/get/amqp-2.2.0.tgz -o /tmp/amqp.tgz \
    || curl -fsSL http://pecl.php.net/get/amqp-2.2.0.tgz -o /tmp/amqp.tgz \
    && pecl install /tmp/amqp.tgz \
    && docker-php-ext-enable amqp \
    && rm -f /tmp/amqp.tgz

RUN docker-php-ext-install sockets

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаём директорию приложения
WORKDIR /var/www/html
