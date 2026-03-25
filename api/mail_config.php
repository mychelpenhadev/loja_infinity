<?php
/**
 * Configurações de E-mail (SMTP)
 * Preencha com seus dados para que o sistema de verificação funcione.
 */
return [
    'host' => 'smtp.gmail.com',         // Ex: smtp.gmail.com
    'auth' => true,
    'username' => 'mychelcajari@gmail.com', // Seu e-mail completo
    'password' => 'lxkusmvxbcnwoggw',       // Senha de aplicativo (sem espaços)
    'secure' => 'tls',                   // tls ou ssl
    'port' => 587,                       // 587 para tls, 465 para ssl
    'from_email' => 'mychelcajari@gmail.com',
    'from_name' => 'Loja Infinity'
];
