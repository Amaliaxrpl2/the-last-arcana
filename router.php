<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rawurldecode($requestPath) : '/';
$pathSegments = explode('/', trim($requestPath, '/'));

if (in_array('.arcana', $pathSegments, true)) {
    http_response_code(404);
    exit;
}

if ($requestPath !== '/' && is_file(__DIR__ . $requestPath)) {
    return false;
}

require __DIR__ . '/index.php';
