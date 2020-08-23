<?php
session_start();
requireValidSession(true);

$exception = null;
$userData = [];

if (count($_POST) === 0 && isset($_GET['update'])) {
    $user = User::getOne(['id' => $_GET['update']]);
    $userData = $user->getValues();
    $userData['password'] = null;
}elseif (count($_POST) > 0) {
    try {
        $dbUser = new User($_POST);
        if ($dbUser->id) {
            $dbUser->update(); // Atualiza
            addSuccessMsg('Usuário atualizado com sucesso!');
            header('Location: usersController.php');
            exit();
        } else {
            $dbUser->insert(); // Insere
            addSuccessMsg('Usuário cadastrado com sucesso!');
        }
        $_POST = []; // limpa o array $_POST
    }catch (Exception $e) {
        $exception = $e;
    } finally {
        $userData = $_POST;
    }
}


loadTemplateView('save_user', $userData + ['exception' => $exception]);