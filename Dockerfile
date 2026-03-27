FROM php:8.2-apache

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala extensões de banco de dados do PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copia tudo para a pasta root do container
COPY . /var/www/html/

# Ajusta as permissões de gravação (necessário para logs e uploads em produção)
RUN chown -R www-data:www-data /var/www/html

# Altera a configuração do Apache explicitamente para escutar na porta da variável de ambiente repassada pelo Railway ao invés do default 80
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf
