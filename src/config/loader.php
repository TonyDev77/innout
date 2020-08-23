<?php
// Método para usar o require dinamicamente
function loadModel($modelName) {
    require_once (MODEL_PATH . "/{$modelName}.php"); // Carrega arquivos da pasta view
}


// Método para carregar a view com parâmetros
function loadView($viewName, $params = array()) {

    // Criando um array para enviar para views/login.php
    if (count($params) > 0) {
        foreach ($params as $key => $value) {
            if (strlen($key) > 0) {
                ${$key} = $value; // Cria um ponteiro duplo (variável de variável)
            }
        }
    }
    require_once (VIEW_PATH . "/{$viewName}.php"); // Carrega arquivos da pasta view
}

// Carrega o template e as views serão carregadas dentro dele
function loadTemplateView($viewName, $params = array()) {
    // Criando um array para enviar para views/login.php
    if (count($params) > 0) {
        foreach ($params as $key => $value) {
            if (strlen($key) > 0) {
                ${$key} = $value; // Cria um ponteiro duplo (variável de variável)
            }
        }
    }

    // Criando um view de WorkingHours
    $user = $_SESSION['user'];
    $workingHours = WorkingHours::loadFromUserAndDate($user->id, date('Y-m-d'));
    $workedInterval = $workingHours->getWorkedInterval()->format('%H:%I:%S');
    $exitTime = $workingHours->getExitTime()->format('H:i:s');
    $activeClock = $workingHours->getActiveClock();

    require_once (TEMPLATE_PATH . "/header.php"); // Carrega arquivos da pasta view
    require_once (TEMPLATE_PATH . "/left.php"); // Carrega arquivos da pasta view
    require_once (VIEW_PATH . "/{$viewName}.php"); // Carrega arquivos da pasta view
    require_once (TEMPLATE_PATH . "/footer.php"); // Carrega arquivos da pasta view
}

function renderTitle($title, $subtitle, $icon = null) {
    require_once(TEMPLATE_PATH . "/title.php");
}
