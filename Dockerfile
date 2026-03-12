FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy all project files into Apache's web root
COPY ./ /var/www/html/

EXPOSE 80

