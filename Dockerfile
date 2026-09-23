FROM php:8.1-apache
# Cài đặt extension MySQL cho PHP và bật mod_rewrite cho MVC
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite
WORKDIR /var/www/html