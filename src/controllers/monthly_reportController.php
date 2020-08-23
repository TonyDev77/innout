<?php
session_start();
requireValidSession();

$currentDate = new DateTime();

$user = $_SESSION['user'];

$selectedUserId = $user->id;
$users = null;
if ($user->is_admin) {
    $users = User::get();
    $selectedUserId =  $_POST['user'] ?   $_POST['user'] : $user->id;
}

$selectedPeriod =  $_POST['period'] ?   $_POST['period'] : $currentDate->format('Y-m');
$periods = [];
for ($yearDiff = 0; $yearDiff <= 2 ; $yearDiff++) {
    $year = date('Y') - $yearDiff;
    for ($month = 12; $month >= 1; $month--) {
        $date = new DateTime("{$year}-{$month}-1");
        $periods[$date->format('Y-m')] = strftime("%B de %Y", $date->getTimestamp());
    }
}

$registries = WorkingHours::getMonthlyReport($selectedUserId, $selectedPeriod); // Recebe todos os registros p/ relatório

$report = []; // Recebe dados consolidados para enviar par a view
$workDay = 0; // Contador para identificar os finais de semana
$sumOfWorkedTime = 0; // Soma todas as horas trabalhadas
$lastDay = getLastDayOfMonth($selectedPeriod)->format('d');

// Varre o intervalo de dias e imprime datas dos dias úteis
for ($day = 1; $day <= $lastDay; $day++) {
    $date = $selectedPeriod . '-' . sprintf('%02d', $day);
    $registry = $registries[$date];

    if (isPastWorkDay($date))
        $workDay++;

    if ($registry) {
        $sumOfWorkedTime += $registry->worked_time; // Recebe e soma apenas as colunas de horas trabalhadas
        array_push($report, $registry); // Copia os dados acima para $registry
    } else {
        array_push($report, new WorkingHours([
                'work_date' => $date,
                'worked_time' => 0
            ])
        );
    }
}

$expectedTime = $workDay * DAILY_TIME; // Quantidade de dias * 8hs
$balance = getTimeStringFromSeconds(abs($sumOfWorkedTime - $expectedTime)); // Dias trabalhados - Dias esperados de trabalho
$sign = ($sumOfWorkedTime >= $expectedTime) ? '+' : '-';

loadTemplateView('monthly_report', [
    'report' => $report,
    'sumOfWorkedTime' => getTimeStringFromSeconds($sumOfWorkedTime),
    'balance' => "{$sign}{$balance}",
    'selectedPeriod' => $selectedPeriod,
    'periods' => $periods,
    'selectedUserId' => $selectedUserId,
    'users' => $users,
]);