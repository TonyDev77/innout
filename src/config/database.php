<?php

class Database {
    // Método para conectar ao BD
    public static function getConnection() {
        // Obtendo o caminho do arquivo de configuração '.ini'
        $envPath = realpath(dirname(__FILE__) . "/../env.ini");
        // Retornando os dados de 'env.ini' em um array (chave=>valor)
        $env = parse_ini_file($envPath);
        //Criando um objeto mysqli c/ parâmetros obtidos do array $env[]
        $conn = new mysqli($env['host'], $env['username'], $env['password'], $env['database']);

        // Testa a conexão
        if ($conn->connect_error) {
            die("Erro: " . $conn->connect_error);
        }
        return $conn;
    }

    // Método para fazer a consulta no BD
    public static function getResultFromQuery($sql) {
        $conn = self::getConnection(); // Conecta ao BD
        $result = $conn->query($sql); // Faz a consulta
        $conn->close(); // Fecha a conexão

        return $result;
    }

    // Executa o sql e retorna o ID inserido
    public static function executeSQL($sql) {
        $conn = self::getConnection();
        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }
        $id = $conn->insert_id;
        $conn->close();
        return $id;
    }
}
