<?php
// Serve arquivos de upload via API
$file = $_GET['file'] ?? '';
if (empty($file)) {
    http_response_code(404);
    exit('Arquivo não encontrado');
}

// Segurança: impedir path traversal
$file = str_replace(['..', '\\'], '', $file);
if (strpos($file, '/') !== 0) {
    $file = '/' . $file;
}

// Tenta encontrar o arquivo em diferentes locais
$paths = [
    __DIR__ . '/../uploads' . $file,
    '/tmp/uploads' . $file,
    getenv('UPLOAD_DIR') ? getenv('UPLOAD_DIR') . $file : null,
];

$found = null;
foreach ($paths as $path) {
    if ($path && file_exists($path) && is_file($path)) {
        $found = $path;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    exit('Arquivo não encontrado');
}

// Serve o arquivo com o content-type correto
$ext = strtolower(pathinfo($found, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
];

$mime = $mimeTypes[$ext] ?? 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . filesize($found));
readfile($found);
