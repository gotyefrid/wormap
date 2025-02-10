<?php

use WorMap\App;

require_once "vendor/autoload.php";

// Получаем путь из запроса
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $requestUri;

// 1. Если файл существует и это статический файл, отдаем его напрямую
if (is_file($filePath) && preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|mp4|webp)$/i', $requestUri)) {
    return false; // Позволяем серверу обработать файл
}

// 2. Если запрашивается view.php, загружаем его отдельно
if ($requestUri === '/view.php') {
    require __DIR__ . '/view.php';
    exit;
}

// 3. Все остальные запросы передаем в приложение
(new App())->run();
