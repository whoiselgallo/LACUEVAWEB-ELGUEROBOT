FROM php:8.2-apache

# 1. Instalar librerías de PostgreSQL y extensiones PHP (PDO pgsql)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Configurar Apache para escuchar en el puerto dinámico de Cloud Run ($PORT o 8080)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 3. Habilitar módulo rewrite de Apache para URLs limpias y enrutamiento de APIs
RUN a2enmod rewrite

# 4. Copiar todo el código de La Cueva del Güero al servidor
COPY . /var/www/html/

# 5. Asegurar permisos correctos para el usuario de Apache
RUN chown -R www-data:www-data /var/www/html/

# 6. Variable de entorno de puerto y exposición para Cloud Run
ENV PORT=8080
EXPOSE 8080