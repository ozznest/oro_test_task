FROM php:8.2-rc-cli
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get upgrade -y \
    && apt-get install apt-utils -y \
#
#    устанавливаем необходимые пакеты
    && apt-get install git zip vim libzip-dev libgmp-dev libffi-dev libssl-dev -y \
#
#    Включаем необходимые расширения
    && docker-php-ext-install -j$(nproc) sockets zip gmp pcntl bcmath ffi \
#
#    Расшерения через pecl ставятся так, то в php8 pecl сейчас отсуствует, так что строки закоментированы
#    && PHP_OPENSSL=yes pecl install ev \
#    && docker-php-ext-enable ev \
#
#    Чистим временные файлы
    && docker-php-source delete \
    && apt-get autoremove --purge -y && apt-get autoclean -y && apt-get clean -y