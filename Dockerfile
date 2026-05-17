# Imagen oficial de PHP con Apache ya integrado.
FROM php:8.3-apache

# Extensiones de PHP necesarias para conectar con MySQL mediante PDO.
RUN docker-php-ext-install pdo pdo_mysql

# Evita un aviso de Apache sobre el ServerName.
RUN echo "ServerName friends4you.samuelciocan.com" >> /etc/apache2/apache2.conf

# Configuracion del sitio: la raiz apunta a la carpeta public/.
COPY docker/apache/friends4you.conf /etc/apache2/sites-available/000-default.conf

# El codigo se monta como volumen desde docker-compose.yml.
WORKDIR /var/www/html
