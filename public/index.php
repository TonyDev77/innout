<?php

// Importa arquivo c/ config. do BD
require_once (dirname(__FILE__, 2) . '/src/config/config.php'); // (local, data, endereços, Database.php, Mode.php)

// Comando para pegar o path da requisição, ignorando os parâmetros
$uri = urldecode( parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Comando que substitui a url requisitada por 'loginController.php'
if (
    $uri === '/' ||
    $uri === '' ||
    $uri === '/index.php'
) {
    $uri = 'day_recordsController.php';
}
//var_dump(CONTROLLER_PATH . "/{$uri}");
require_once (CONTROLLER_PATH . "/$uri");


