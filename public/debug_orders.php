<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Order;

$orders = Order::orderBy('created_at', 'desc')->limit(5)->get();
header('Content-Type: application/json');
echo json_encode($orders, JSON_PRETTY_PRINT);
