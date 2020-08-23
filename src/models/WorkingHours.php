<?php
class WorkingHours extends Model {
    protected static $tableName = 'working_hours';
    protected static $columns = [
        'id',
        'user_id',
        'work_date',
        'time1',
        'time2',
        'time3',
        'time4',
        'worked_time'
    ];

    // Carregar dados e data a partir do usuario
    public static function loadFromUserAndDate($userId, $workDate) {
        $registry = self::getOne(['user_id' => $userId, 'work_date' => $workDate]);
        if (!$registry) {
            $registry = new WorkingHours([
                'user_id' => $userId,
                'work_date' => $workDate,
                'worked_time' => 0
            ]);
        }

        return $registry;
    }

    // Busca o próximo atributo da tabela para bater o ponto
    public function getNextTime() {
        if (!$this->time1) return 'time1';
        if (!$this->time2) return 'time2';
        if (!$this->time3) return 'time3';
        if (!$this->time4) return 'time4';
        return null;
    }

    // Ajuda o JS a saber qual intervalo incrementar
    function getActiveClock() {
        $nextTime = $this->getNextTime();
        if ($nextTime === 'time1' || $nextTime === 'time3') { // O JS incrementará o horário de saída
            return 'exitTime';
        } elseif ($nextTime === 'time2' || $nextTime === 'time4') {
            return 'workedInterval';
        } else {
            return null;
        }
    }

    // Efetiva o batimento do ponto
    public function innout($time) {
        $timeColumn = $this->getNextTime();
        if (!$timeColumn) {
            throw new AppException('Você já fez todos os batimentos do dia!');
        }
        $this->$timeColumn = $time;
        $this->worked_time = getSecondsFromDateIntervals($this->getWorkedInterval());
        // Se o id já estiver setado, o registro já existe, e será atualizado
        if ($this->id) {
            $this->update(); // Método da classe 'Model'
        } else {
            $this->insert(); // Se não houver, insere um novo registro
        }
    }

    // Pega as 4 stings de datas do BD e converte pra data
    private function getTimes() {
        $times = [];

        $this->time1 ? array_push($times, getDateFromString($this->time1)) : array_push($times, null);
        $this->time2 ? array_push($times, getDateFromString($this->time2)) : array_push($times, null);
        $this->time3 ? array_push($times, getDateFromString($this->time3)) : array_push($times, null);
        $this->time4 ? array_push($times, getDateFromString($this->time4)) : array_push($times, null);

        return $times;
    }

    // Retorna os usuários ausente no dia atual
    public static function getAbsentUsers() {
        $today = new DateTime();
        $result = Database::getResultFromQuery("
            SELECT name FROM users
            WHERE end_date is NULL
            AND id NOT IN (
                SELECT user_id FROM working_hours
                WHERE work_date = '{$today->format('Y-m-d')}'
                AND time1 IS NOT NULL
            )"
        );

        $absentUsers = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($absentUsers, $row['name']);
            }
        }

        return $absentUsers;
    }

    // Retorna soma das horas trabalhadas no mês
    public static function getWorkedTimeInMonth($yearAndMonth) {
        $startDate = (new DateTime("{$yearAndMonth}-1"))->format('Y-m-d');
        $endDate = getLastDayOfMonth($yearAndMonth)->format('Y-m-d');
        $result = static::getResultSetFromSelect([
            'raw' => "work_date BETWEEN '{$startDate}' AND '{$endDate}'"
        ], "sum(worked_time) AS sum");

        return $result->fetch_assoc()['sum'];
    }

    // Retorna um intervalo de horas trabalhadas no mês
    public static function getMonthlyReport($userId, $date) {
        $registries = []; // Receberá os intervalos de datas

        // Seta o início e o fim da busca
        $startDate = getFirstDayOfMonth($date)->format('Y-m-d');
        $endDate = getLastDayOfMonth($date)->format('Y-m-d');

        // Executa a busca baseada nos parâmetros acima usando SQL abaixo
        $result = WorkingHours::getResultSetFromSelect([
            'user_id' => $userId,
            'raw' => "work_date BETWEEN '{$startDate}' AND '{$endDate}'"
        ]);

        // Preenche o array $registries com o resultado da query
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $registries[$row['work_date']] = new WorkingHours($row);
            }
        }

        return $registries;
    }

    // Pega o intervalo de horas trabalhadas no dia
    function getWorkedInterval() {
        [$t1, $t2, $t3, $t4] = $this->getTimes();

        $morning = new DateInterval('PT0S'); // P: periodo, T: time, 0: quantidade, S: segundos
        $afternoon = new DateInterval('PT0S');

        // Calcula os intervalos de manhã e tarde
        if ($t1) $morning = $t1->diff(new DateTime());
        if ($t2) $morning = $t1->diff($t2);
        if ($t3) $afternoon = $t3->diff(new DateTime());
        if ($t4) $afternoon = $t3->diff($t4);

        return sumIntervals($morning, $afternoon);
    }

    // Calcula o intervalo do almoço
    function getLunchInterval() {
        [, $t2, $t3,] = $this->getTimes();
        $lunchInterval = new DateInterval('PT0S');

        if ($t2) $lunchInterval = $t2->diff(new DateTime());
        if ($t3) $lunchInterval = $t2->diff($t3);

        return  $lunchInterval;
    }

    // Retorna o horário de saída
    function getExitTime() {
        [$t1,,, $t4] = $this->getTimes();
        $workday = DateInterval::createFromDateString('8 hours');

        // Soma as horas que faltam cumprir
        if (!$t1) { // Se não bateu nenhum ponto
            return (new DateTimeImmutable())->add($workday);
        } elseif ($t4) { // Se bateu todos os pontos
            return $t4;
        } else {
            $total = sumIntervals($workday, $this->getLunchInterval());
            return $t1->add($total);
        }
    }

    // Calcula Saldo
    function getBalance() {
        if (!$this->time1 && !isPastWorkDay($this->work_date))
            return ''; // Não calucla esse dia
        if ($this->worked_time == DAILY_TIME)
            return '-'; // Não exibe os dinal + ou -
        $balance = $this->worked_time - DAILY_TIME;  // Calcula horas trabalhadas
        $balanceString = getTimeStringFromSeconds(abs($balance)); // Formata segundos
        $sign = $this->worked_time >= DAILY_TIME ? '+' : '-';
        return "{$sign}{$balanceString}";
    }
}