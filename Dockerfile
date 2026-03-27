FROM php:8.2-apache

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala extensões de banco de dados do PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copia tudo para a pasta root do container
COPY . /var/www/html/

# Ajusta as permissões de gravação
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80 nativamente, o Railway detectará automaticamente essa porta e fará o roteamento do tráfego web
EXPOSE 80
