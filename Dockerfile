FROM php:8.2-apache

# Força o desligamento de modos conflitantes do Apache e garante o correto para PHP
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala extensões de banco de dados do PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copia tudo para a pasta root do container
COPY . /var/www/html/

# Ajusta as permissões de gravação
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
