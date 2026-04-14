@echo off
echo ====================================================
echo    Iniciando Servidor Laravel (PHP 8.5) e Vite...
echo ====================================================

REM Definindo variáveis de ambiente temporárias
set PHP_BIN=C:\xampp\php-8.5.5-nts-Win32-vs17-x64\php.exe
set NODE_HOME=C:\xampp\node-v22.14.0-win-x64\
set PATH=%NODE_HOME%;%PATH%

REM Abrir o servidor Backend do Laravel
echo [1] Iniciando o backend na porta 8000...
start "Servidor Laravel (Backend)" cmd /k "%PHP_BIN% artisan serve"

REM Abrir o compilador Frontend do Vite
echo [2] Iniciando o frontend via Vite...
start "Servidor Vite (Frontend)" cmd /k "npm run dev"

echo.
echo Tudo iniciado! O seu projeto pode ser acessado em: http://localhost:8000
echo.
pause
