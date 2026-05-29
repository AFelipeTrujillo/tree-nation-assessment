FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    bash \
    sqlite \
    sqlite-dev \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN chmod +x docker/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["sh", "docker/entrypoint.sh"]