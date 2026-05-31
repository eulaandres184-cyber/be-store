FROM php:8.2-fpm-alpine

# Instalar extensiones necesarias para Laravel y MySQL
RUN apk add --no-cache nginx wget mariadb-client shadow \
    && docker-php-ext-install pdo_mysql

# Configurar directorio de trabajo
WORKDIR /var/www/html
COPY . .

# Instalar Composer
RUN curl -sS https://getcomposer.org | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos para Laravel
RUN chown -R rw-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar Nginx
COPY ./nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD ["sh", "-c", "nginx && php-fpm"]
