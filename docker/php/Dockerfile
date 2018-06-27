FROM php:7.2-fpm

RUN docker-php-ext-install -j$(nproc) mysqli pdo_mysql
VOLUME /var/www/html/site/web
VOLUME /var/www/html/site/data
VOLUME /var/www/html/site/vendor
VOLUME /var/www/html/site/config
