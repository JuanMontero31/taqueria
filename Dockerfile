FROM php:apache

# Habilita las extensiones necesarias, como MySQL
RUN docker-php-ext-install mysqli

COPY . /var/www/html/

EXPOSE 80