<?php
session_start();
requireValidSession();

// Criando e formatando data atual
$date = (new Datetime())->getTimestamp();
$today = strftime("%d de %B de %Y", $date);

// Carregando template
loadTemplateView('day_records', ['today' => $today]);