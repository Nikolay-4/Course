FROM php:8.0.2-fpm

WORKDIR "/task"

ARG DOCKER_GID=1000
ARG DOCKER_UID=1000

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libzip-dev \
        libpng-dev \
        libpq-dev \
        && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


RUN docker-php-ext-install bcmath zip pdo_pgsql exif sockets

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install redis \
    && docker-php-ext-enable redis.so

RUN curl https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer | php -- --quiet \
    && mv composer.phar /usr/local/bin/composer

RUN groupadd -g $DOCKER_GID dev
RUN useradd -g $DOCKER_GID -u $DOCKER_UID dev
RUN mkdir /home/dev
RUN chown -R dev:dev /home/dev

COPY ./TaskTrackerService /task
COPY "docker/php-fpm/php-ini-overrides.ini" "/usr/local/etc/php/conf.d/99-overrides.ini"


# Для Тинкера
RUN mkdir -p /var/www/.config/psysh
RUN chown 33:33 /var/www/.config/psysh
RUN chown 33:33 /task/bootstrap/cache

USER root
