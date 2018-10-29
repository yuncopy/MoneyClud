FROM hub.c.163.com/library/php:7.1.9-fpm
MAINTAINER yuncopy <yuncopy@sina.com>

WORKDIR /home/www

RUN apt-get update && apt-get install -y libmcrypt-dev libmemcached-dev mcrypt libbz2-dev libpng-dev \
  && pecl install -o -f mongodb swoole redis mcrypt memcached \
  && docker-php-ext-enable mongodb swoole redis mcrypt memcached \
  && docker-php-ext-install bcmath opcache bz2 gd iconv mysqli pdo pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/* \
  && rm -rf /tmp/pear ~/.pearrc \
  && curl https://getcomposer.org/composer.phar -o /usr/bin/composer \
  && chmod a+x /usr/bin/composer \
  && composer config -g repo.packagist composer https://packagist.phpcomposer.com

COPY sources.list /etc/apt/sources.list
COPY php.ini /usr/local/etc/php
RUN apt-get update && apt-get install -y vim
