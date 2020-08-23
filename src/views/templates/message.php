<?php

// Array para carregar as informações de erros
$errors = [];

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} elseif ($exception) {
    $message = [
        'type' => 'error',
        'message' => $exception->getMessage()
    ];

    // Atribui para "$errors" as mensagens de "ValidationException"
    if (get_class($exception) === 'ValidationException') {
        $errors = $exception->getErrors();
    }
}

// Condicionar o css
$alertType = '';
if ($message['type'] === 'error') {
    $alertType = 'danger';
} else {
    $alertType = 'success';
}
?>

<!-- FORMATANDO OS ALERTAS COM CSS -->
<?php if ($message): ?>

    <div role="alert" class="my-3 alert alert-<?= $alertType ?>" >
        <?= $message['message'] ?>
    </div>

<?php endif; ?>