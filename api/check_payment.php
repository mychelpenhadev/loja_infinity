<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Payment ID is required']);
    exit;
}

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'SDK do Mercado Pago não instalado.']);
    exit;
}
require_once $autoloadPath;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

$envPath = __DIR__ . '/../.env';
$mpToken = "APP_USR-1227909029364789-032218-383ad6729ee18217f90fbbd7cbaff011-3284167937";
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    if ($env !== false && !empty($env['MP_ACCESS_TOKEN'])) {
        $mpToken = $env['MP_ACCESS_TOKEN'];
    }
}
MercadoPagoConfig::setAccessToken($mpToken);

$client = new PaymentClient();

try {
    $payment = $client->get($id);
    
    echo json_encode([
        'id' => $payment->id,
        'status' => $payment->status
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao consultar pagamento: ' . $e->getMessage()]);
}
?>
