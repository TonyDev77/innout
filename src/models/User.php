<?php

// Classe com os atributos da entidade 'users'
class User extends Model {
    protected static $tableName = 'users';
    protected static $columns = [
        'name',
        'password',
        'email',
        'start_date',
        'end_date',
        'is_admin'
    ];

    // Conta todos os usuário ativos via sql
    public static function getActiveUsersCount() {
        return static::getCount(['raw' => 'end_date IS NULL']);
    }

    // Sobrescreve o método 'insert()' da classe Model
    public function insert() {
        $this->validate();
        $this->is_admin = $this->is_admin ? 1 : 0;
        if (!$this->end_date)
            $this->end_date = null;

        parent::insert();
    }

    // Sobrescreve o método 'update'da classe Model
    public function update() {
        $this->validate();
        $this->is_admin = $this->is_admin ? 1 : 0;
        if (!$this->end_date)
            $this->end_date = null;

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        parent::update();
    }

    // Validação dos formulários
    private function validate() {
        $errors = [];
        if (!$this->name)
            $errors['name'] = 'Nome é um campo obrigatório!';

        if (!$this->email) {
            $errors['email'] = 'Email é um campo obrigatório!';
        }elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido!';
        }

        if (!$this->start_date) {
            $errors['start_date'] = 'Data de Admissão é um campo obrigatório!';
        } elseif(!DateTime::createFromFormat('Y-m-d', $this->start_date)) {
            $errors['start_date'] = 'Data de Admissão deve estar no padrão dd/mm/aaaa';
        }

        if ($this->end_date && !DateTime::createFromFormat('Y-m-d', $this->end_date)) {
            $errors['end_date'] = 'Data de desligamento deve estar no padrão dd/mm/aaaa';
        }

        if (!$this->password) {
            $errors['password'] = 'Senha é um campo obrigatório!';
        }

        if (!$this->confirm_password) {
            $errors['confirm_password'] = 'Confirmação de senha é um campo obrigatório!';
        }

        if ($this->password && $this->confirm_password && $this->password !== $this->confirm_password) {
            $errors['password'] = 'As senhas não são iguais';
            $errors['confirm_password'] = 'As senhas não são iguais';
        }

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}