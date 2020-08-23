<?php

// Transforma strings para o formato data
function getDateAsDateTime($date) {
    return is_string($date) ? new DateTime($date): $date;
}

// Testa se é um final de semana
function isWeekend($date) {
    $inputDate = getDateAsDateTime($date);
    return $inputDate->format('N') >= 6;
}

// Testa se uma data é maior que outra
function isBefore($date1, $date2) {
    $inputDate1 = getDateAsDateTime($date1);
    $inputDate2 = getDateAsDateTime($date2);
    return $inputDate1 <= $inputDate2;
}

// Mostra o próximo dia
function getNextDay($date) {
    $inputDate = getDateAsDateTime($date);
    $inputDate->modify('+1 day');
    return $inputDate;
}

// Soma os intervalos
function sumIntervals($interval1, $interval2) {
    $date = new DateTime("00:00:00");
    $date->add($interval1);
    $date->add($interval2);
    return (new DateTime("00:00:00"))->diff($date);
}

// Subtrai os intervalos
function subtractIntervals($interval1, $interval2) {
    $date = new DateTime("00:00:00");
    $date->add($interval1);
    $date->sub($interval2);
    return (new DateTime("00:00:00"))->diff($date);
}

// Converte intervalo para fomrato de data
function getDateFromInterval($interval) {
    return new DateTimeImmutable($interval->format('%H:%i:%s')); // Data imutável
}

// Converte string para fomrato de data
function getDateFromString($str) {
    return DateTimeImmutable::createFromFormat("H:i:s", $str);
}

// Retorna o primeito dia do mês baseado na consulta
function getFirstDayOfMonth($date) {
    $time = getDateAsDateTime($date)->getTimestamp();
    return new DateTime(date('Y-m-1', $time));
}

// Retorna o último dia do mês baseado em uma data
function getLastDayOfMonth($date) {
    $time = getDateAsDateTime($date)->getTimestamp();
    return new DateTime(date('Y-m-t', $time));
}

// Retorna quantidade de segundos
function getSecondsFromDateIntervals($interval) {
    $d1 = new DateTimeImmutable();
    $d2 = $d1->add($interval);

    return $d2->getTimestamp() - $d1->getTimestamp();
}

// Valida que é um dia trabalhado no passado
function isPastWorkDay($date) {
    return !isWeekend($date) && isBefore($date, new DateTime());
}

// Transforma total de segundos em hora:minuto:segundo
function getTimeStringFromSeconds($seconds) {

    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds - (($h * 3600) + ($m * 60));

    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

//
function formatDateWidthLocale($date, $pattern) {
    $time = getDateAsDateTime($date)->getTimestamp();
    return strftime($pattern, $time);
}
