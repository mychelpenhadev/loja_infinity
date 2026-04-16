#!/bin/bash

# Se a APP_KEY não estiver no ambiente, usa uma fallback (melhor configurar no painel do Railway!)
if [ -z "$APP_KEY" ]; then
    echo "Aviso: APP_KEY não encontrada. Usando chave padrão."
    export APP_KEY="base64:jP21cJxk75i8Fpj5xJ8br/e7Wu/6zv5mrIdoI+Afc2s="
fi

# Cria as pastas necessárias
echo "[Setup] Criando diretórios de upload e storage..."
mkdir -p public/uploads/banners public/uploads/produtos public/uploads/clientes
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# Garante permissões de escrita (Crucial para o Railway)
echo "[Setup] Ajustando permissões (chmod 777)..."
chmod -R 777 public/uploads storage bootstrap/cache

# Limpa caches que podem conter caminhos locais do build
echo "[Laravel] Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Tenta rodar migrações (usamos driver 'array' e forçamos a limpeza da tabela de sessões se ela estiver corrompida)
echo "[Laravel] Reparando banco de dados..."
SESSION_DRIVER=array php artisan tinker --execute="Schema::dropIfExists('sessions')"
SESSION_DRIVER=array php artisan migrate --force || echo "Aviso: Falha ao rodar migrações."

# Inicia o servidor
echo "[Server] Iniciando Laravel na porta $PORT..."
php artisan serve --host 0.0.0.0 --port ${PORT:-8080}
