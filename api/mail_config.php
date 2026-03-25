<?php
/**
 * Configurações de E-mail (SMTP)
 * Preencha com seus dados para que o sistema de verificação funcione.
 */
return [
    'host' => 'smtp.gmail.com',         // Ex: smtp.gmail.com
    'auth' => true,
    'username' => 'seu-email@gmail.com', // Seu e-mail completo
    'password' => 'sua-senha-app',       // Senha de aplicativo (não a senha comum)
    'secure' => 'tls',                   // tls ou ssl
    'port' => 587,                       // 587 para tls, 465 para ssl
    'from_email' => 'seu-email@gmail.com',
    'from_name' => 'Infinix Loja'
];
