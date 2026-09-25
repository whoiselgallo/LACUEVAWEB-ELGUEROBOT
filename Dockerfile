FROM php:8.2-apache

# 1. Instalar librerías de PostgreSQL y extensiones PHP (PDO pgsql)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Habilitar módulo rewrite de Apache para URLs limpias y enrutamiento de APIs
RUN a2enmod rewrite

# 4. Copiar todo el código de La Cueva del Güero al servidor
COPY . /var/www/html/

# 5. Asegurar permisos correctos para el usuario de Apache
RUN chown -R www-data:www-data /var/www/html/

# 6. Apache se configura al arrancar con el PORT que entregue Render
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENV PORT=10000
EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]