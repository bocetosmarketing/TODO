<?php
/**
 * Descarga automática del último release de Conversa desde GitHub
 * Compatible con verificaciones de WooCommerce
 */

// Si es un HEAD request (WooCommerce verificando), responder como si fuera un ZIP válido
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'HEAD') {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="conversa.zip"');
    header('Content-Length: 8388608'); // Simular 8MB
    header('Accept-Ranges: bytes');
    http_response_code(200);
    exit;
}

// Obtener el último release de GitHub API
$api_url = 'https://api.github.com/repos/bocetosmarketing/conversa-bot/releases/latest';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'WooCommerce-Download-Script');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Si falla la API, usar fallback
if ($response === false || $http_code !== 200) {
    header('Location: https://github.com/bocetosmarketing/conversa-bot/archive/refs/heads/main.zip');
    exit;
}

$release = json_decode($response, true);

// Preparar URL y nombre comercial del archivo
$download_url = null;
$filename = 'conversa.zip';

if (isset($release['zipball_url']) && isset($release['tag_name'])) {
    $download_url = $release['zipball_url'];
    $filename = 'conversa-' . $release['tag_name'] . '.zip';
} else {
    $download_url = 'https://github.com/bocetosmarketing/conversa-bot/archive/refs/heads/main.zip';
    $filename = 'conversa-latest.zip';
}

// Configurar headers para descarga con nombre comercial
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Streaming del archivo desde GitHub sin guardarlo en disco
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $download_url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'WooCommerce-Download-Script');
curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutos para archivos grandes
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    echo $data;
    flush();
    return strlen($data);
});

curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Si falla el streaming, mostrar error
if ($http_code !== 200) {
    http_response_code(500);
    die("Error downloading file");
}

exit;
