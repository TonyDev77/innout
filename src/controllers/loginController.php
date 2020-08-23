<?php
loadModel('login');
session_start(); // Iniciando Sessão

$exception = null;

// Recebe os dados da view e valida
if (count($_POST) > 0) {
    $login = new Login($_POST);
    // Verifica se usuário está logado
    try {
        $user = $login->checkLogin();
        $_SESSION['user'] = $user;
        header("Location: /day_recordsController.php");
    } catch (AppException $e) {
         $exception = $e;
    }
}

// Carrega a view com parâmetros vindos do formulário via POST
loadView('login', $_POST + ['exception' => $exception]);