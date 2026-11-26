# Imagens de produção. O código e o vendor ficam dentro da imagem (sem bind mount).
#   target app: PHP-FPM 7.4 com o Laravel
#   target web: nginx com o public/ e a config de produção

FROM php:7.4-fpm AS app

# PHP 7.4 só existe sobre Debian 11, que saiu do suporte e foi para archive.debian.org
RUN sed -i 's|http://deb.debian.org|http://archive.debian.org|g' /etc/apt/sources.list \
    && apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libonig-dev libxml2-dev libzip-dev unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/prod/php.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh

WORKDIR /var/www
ENV COMPOSER_ALLOW_SUPERUSER=1

# Dependências primeiro, para o cache de camadas não reinstalar a cada mudança de código
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]


FROM nginx:1.29-alpine AS web

COPY docker/prod/nginx.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/public
