FROM php:8.2-apache

# Habilita o mod_rewrite do Apache para usar o arquivo .htaccess corretamente
RUN a2enmod rewrite

# Instala extensões de banco de dados do PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copia tudo para a pasta root do container
COPY . /var/www/html/

# Ajusta as permissões
RUN chown -R www-data:www-data /var/www/html

# Substitui a porta padrao do apache pela $PORT do Railway somenta na hora de rodar
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground
