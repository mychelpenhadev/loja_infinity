#!/bin/bash
# O Railway injeta a variável $PORT aqui. Precisamos forçar o Apache a ouvir nela.
if [ -z "$PORT" ]; then
    export PORT=80
fi

sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf

# Inicia o daemon original do Apache
apache2-foreground
