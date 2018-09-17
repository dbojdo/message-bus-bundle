FROM php:7.2-cli

RUN apt-get update && apt-get install -y \
       libpq-dev \
       git \
       subversion \
       zlib1g-dev \
       zip \
   && docker-php-ext-install -j$(nproc) iconv bcmath \
   && docker-php-ext-install -j$(nproc) zip \
   && pecl install apcu \
   && docker-php-ext-enable apcu \
   && pecl install xdebug \
   && docker-php-ext-enable xdebug

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN ln -s /usr/local/bin/php /usr/bin/php

WORKDIR /var/www/html
CMD composer update -n
