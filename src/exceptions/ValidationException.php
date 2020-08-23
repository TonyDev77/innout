<?php
class ValidationException extends AppException {

    private $errors = [];

    // CONSTRUTOR
    public function __construct($errors = [], $message = "Erros de Validação", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous); // Construtor da superclasse
        $this->errors = $errors; // Construtor local
    }

    // GET
    public function getErrors() {
        return $this->errors;
    }

    // MÉTODO que retornará erros relacionado a cada campo
    public function get($att) {
        return $this->errors[$att];
    }
}