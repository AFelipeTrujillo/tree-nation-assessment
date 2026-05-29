#!/usr/bin/env sh

set -e

cd /app

mkdir -p var

if [ ! -f .env ]; then
  cp .env.example .env
fi

composer install \
  --no-interaction \
  --prefer-dist \
  --no-progress

php bin/migrate.php

exec php -S 0.0.0.0:8080 -t public