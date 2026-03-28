<?php
// Script para migrar imagens base64 do banco para arquivos físicos
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'db.php';

$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

echo "=== Migração de Imagens Base64 para Arquivos Físicos ===\n\n";

// Busca produtos com imagem base64
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image LIKE 'data:image/%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Produtos com imagem base64 encontrados: " . count($products) . "\n\n";

$migrated = 0;
$errors = 0;

foreach ($products as $product) {
    $id = $product['id'];
    $imageData = $product['image'];
    
    // Extrai o tipo e os dados
    if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $imageData, $matches)) {
        $ext = $matches[1];
        $base64Data = $matches[2];
        
        // Converte extensão
        $ext = strtolower($ext);
        if ($ext === 'jpeg') $ext = 'jpg';
        
        // Gera nome do arquivo
        $filename = "product_{$id}_" . uniqid() . ".{$ext}";
        $filepath = $uploadDir . $filename;
        
        // Decodifica e salva
        $binaryData = base64_decode($base64Data);
        if ($binaryData === false) {
            echo "Erro ao decodificar base64 do produto ID=$id\n";
            $errors++;
            continue;
        }
        
        if (file_put_contents($filepath, $binaryData)) {
            $relativePath = "uploads/products/" . $filename;
            
            // Atualiza o banco com o caminho do arquivo
            $updateStmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            $updateStmt->execute([$relativePath, $id]);
            
            $sizeKB = round(strlen($binaryData) / 1024, 1);
            echo "OK: ID=$id | {$product['name']} | {$sizeKB}KB -> {$relativePath}\n";
            $migrated++;
        } else {
            echo "ERRO: Não foi possível salvar arquivo para ID=$id\n";
            $errors++;
        }
    } else {
        echo "AVISO: ID=$id | Formato não reconhecido\n";
        $errors++;
    }
}

echo "\n=== Resumo ===\n";
echo "Migrados: $migrated\n";
echo "Erros: $errors\n";

// Limpa cache
$cacheDir = __DIR__ . '/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . "/*.json");
    foreach ($files as $f) {
        unlink($f);
    }
    echo "Cache limpo: " . count($files) . " arquivos removidos\n";
}

echo "\nConcluído!\n";
