<?php

class Model
{
    protected static $tableName = ''; // Associa o nome da tabela
    protected static $columns = []; // Associa o nome das colunas
    protected $values = []; // Associa o array passado como parâmetro no construtor dessa classe

    function __construct($arr, $sanitize = true)
    { // Construtor
        $this->loadFromArray($arr, $sanitize);
    }

    public function loadFromArray($arr, $sanitize = true)
    { // Envia para o construtor o array de dados
        if ($arr) {
            $conn = Database::getConnection();
            foreach ($arr as $key => $value) {
                $cleanValue = $value;
                // Protegendo as entradas contra injections (sanitizando)
                if ($sanitize && isset($cleanValue)) {
                    $cleanValue = strip_tags(trim($cleanValue)); // limpa e exclui tags e scripts html
                    $cleanValue = htmlentities($cleanValue, ENT_NOQUOTES); // exclui entidades html
                    $cleanValue = mysqli_real_escape_string($conn, $cleanValue); // Exlclui comandos sql
                }
                $this->$key = $cleanValue; // Usa o método __set
            }
            $conn->close();
        }
    }

    // Retorna o array
    public function __get($key) {
        return $this->values[$key]; // Retorna o array '$values[]' e os valores de '$value' na posição '$key'
    }

    // Seta chave e valor do array
    public function __set($key, $value) {
        $this->values[$key] = $value; // Array '$values[]' na posição '$key', recebe '$value'
    }

    // Retorna todos os valores do array interno $this
    public function getValues() {
        return $this->values;
    }

    /* =======================================================================
     * Método para criar um SELECT dinâmico usando os dados da classe 'User'
     * =======================================================================
     */
    // Função para obter todos os dados do BD e colocar dentro de um array, para em fim, por dentro de uma classe
    public static function getOne($filters = [], $columns = '*')
    {
        $class = get_called_class(); // Indica qual classe chamou este método get()
        $result = static::getResultSetFromSelect($filters, $columns); // Recebe os dados do BD

        return $result ? new $class($result->fetch_assoc()) : null; // Ternária para criar uma objeto da classe User
    }

    // Função para obter todos os dados do BD e colocar dentro de um array, para em fim, por dentro de uma classe
    public static function get($filters = [], $columns = '*')
    {
        $objects = []; // Array para receber os dados;
        $result = static::getResultSetFromSelect($filters, $columns); // Recebe os dados do BD
        if ($result) {
            $class = get_called_class(); // Indica qual classe chamou este método get()
            while ($row = $result->fetch_assoc()) { // Associa os dados ao array '$row'
                // Passa o array em '$objects' para o construtor de 'Model', herdado por 'User'.
                // Isso só é possível por causa da função atribuída a '$class'
                array_push($objects, new $class($row));
            }
        }
        return $objects;
    }


    // Método SELECT
    public static function getResultSetFromSelect($filters = [], $columns = '*')
    {
        $sql = "SELECT ${columns} FROM " . static::$tableName . static::getFilters($filters);

        $result = Database::getResultFromQuery($sql); // Faz a consulta no BD na classe 'Database'

        if ($result->num_rows === 0) {
            return null;
        } else {
            return $result;
        }
    }

    // --------------------------- INSERIR -----------------------------------------
    // Cria sql pra INSERIR dados na tabela e identificar o id
    public function insert() {
        $sql = "INSERT INTO " . static::$tableName . " ("
            . implode(",", static::$columns) . ") VALUES ("; // Separa o array em strings usando a vírgula "," como parâmetro
        foreach (static::$columns as $col) {
            $sql .= static::getFormatedValue($this->$col) . ",";
        }
        $sql[strlen($sql) - 1] = ')'; // Substitui $sql[?] por ")"
        $id = Database::executeSQL($sql);
        $this->id = $id;
    }

    // --------------------------- ATUALIZAR -----------------------------------------
    //Cria sql e atualiza a tabela
    public function update() {
        $sql = "UPDATE " . static::$tableName . " SET ";
        foreach (static::$columns as $col) {
            $sql .= " ${col} = " . static::getFormatedValue($this->$col) . ",";
        }
        $sql[strlen($sql) - 1] = ' ';
        $sql .= "WHERE id = {$this->id}";
        Database::executeSQL($sql);
    }

    // --------------------------- ATUALIZAR -----------------------------------------
    public static function deleteById($id) {
        $sql = "DELETE FROM " . static::$tableName . " WHERE id = {$id}";
        Database::executeSQL($sql);
    }

    // Adicionando 'WHERE' e 'AND'
    private static function getFilters($filters)
    {
        $sql = '';
        if (count($filters) > 0) {
            $sql = $sql . " WHERE 1 = 1"; // WHERE
            foreach ($filters as $column => $value) { // AND
                if ($column == 'raw') {
                    $sql .= " AND {$value}";
                } else {
                    $sql .= " AND ${column} = " . self::getFormatedValue($value);
                }
            }
        }
        return $sql;
    }

    // ADD CONTAGEM AO QUERY SQL
    public static function getCount($filters = []) {
        $result = static::getResultSetFromSelect($filters, ('count(*) AS count'));
        return $result->fetch_assoc()['count'];
    }

    // Tratando o atributo '$value'
    private static function getFormatedValue($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (gettype($value) === 'string') {
            return "'${value}'"; // As aspas duplas são pra poder interpolar
        } else {
            return $value;
        }
    }

}

