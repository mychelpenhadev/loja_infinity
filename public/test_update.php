<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$user = \App\Models\User::first();
echo "Old Name: " . $user->name . "\n";
$user->name = "Adm Teste";
$user->save();

$user2 = \App\Models\User::first();
echo "New Name: " . $user2->name . "\n";
