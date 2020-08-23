<?php
// Validando a Sessão
function requireValidSession($requiresAdmin = false) {
    $user = $_SESSION['user'];
    if (!isset($user)) {
        header("Location: loginController.php");
        exit();
    } elseif ($requiresAdmin && !$user->is_admin) {
        addErrorMsg("Acesso negado!");
        header("Location: day_recordsController.php");
        exit();
    }
}