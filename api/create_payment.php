<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data format or missing items']);
    exit;
}

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'SDK do Mercado Pago não instalado. Execute o composer.']);
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

$total = 0;
foreach ($data['items'] as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}

$userId = $_SESSION['user_id'] ?? null;
$payerEmail = 'dummy_email_pix_' . time() . '@test.com'; // Fallback
$payerName = 'Cliente Infinity';
$payerCpf = null;

if ($userId) {
    try {
        $stmt = $pdo->prepare("SELECT name, email, cpf FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $payerEmail = $user['email'];
            $payerName = $user['name'];
            if(!empty($user['cpf'])) {
                // Remover caracteres especiais do CPF
                $payerCpf = preg_replace('/[^0-9]/', '', $user['cpf']);
            }
        }
    } catch(Exception $e) {}
}

$client = new PaymentClient();

// O Mercado Pago Pix é MUITO rígido com CPF e formato de nome
$fallbackCpf = "19119119100"; // CPF de teste válido no algoritmo
$finalCpf = $payerCpf ? $payerCpf : $fallbackCpf;

// Payer first_name e last_name
$nameParts = explode(' ', trim($payerName));
$firstName = $nameParts[0];
$lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : 'Sobrenome';

// Dados para a requisicao do MP
$paymentRequest = [
    "transaction_amount" => (float)$total,
    "description" => "Compra na Infinity Variedades",
    "payment_method_id" => "pix",
    "payer" => [
        "email" => $payerEmail,
        "first_name" => $firstName,
        "last_name" => $lastName,
        "identification" => [
            "type" => "CPF",
            "number" => $finalCpf
        ]
    ]
];

try {
    $payment = $client->create($paymentRequest);

    if (!$payment) {
        throw new Exception("Falha de Comunicação com o Mercado Pago.");
    }
    
    if (isset($payment->error)) {
        throw new Exception($payment->error->message ?? "Erro interno");
    }

    echo json_encode([
        'id' => $payment->id,
        'status' => $payment->status,
        'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? '',
        'qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? ''
    ]);
} catch (\MercadoPago\Exceptions\MPApiException $e) {
    http_response_code(400);
    $response = $e->getApiResponse();
    $content = $response ? $response->getContent() : null;
    $msg = $content ? json_encode($content) : $e->getMessage();
    echo json_encode(['error' => "Erro do Mercado Pago: " . $msg]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}
?>
