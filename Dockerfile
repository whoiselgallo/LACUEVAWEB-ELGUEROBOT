FROM php:8.2-apache

# Instalar dependencias de PostgreSQL y el driver PDO para PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar el módulo rewrite de Apache (para APIs y enrutamiento limpio)
RUN a2enmod rewrite

# Copiar todos los archivos del proyecto al servidor web de Apache
COPY . /var/www/html/

# Asegurar los permisos correctos para el servidor de Apache
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80 (el puerto estándar de Apache)
EXPOSE 80