FROM php:8.3-apache

# Document root = répertoire public (MVC)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Dossier data : créé pour le volume nommé (permissions pour www-data)
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

RUN a2enmod rewrite
