FROM php:8.4-apache

RUN apt-get update && \
    docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
