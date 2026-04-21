<?php

class AuthView
{
    private array $errors;
    private string $activeForm;

    public function __construct()
    {
        $this->errors = [
            'login' => $_SESSION['login_error'] ?? '',
            'register' => $_SESSION['register_error'] ?? ''
        ];

        $this->activeForm = $_SESSION['active_form'] ?? 'login';

        unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getActiveForm(): string
    {
        return $this->activeForm;
    }

    public function showError(string $input_error): string
    {
        return $input_error !== '' ? '<p class="error-message">' . htmlspecialchars($input_error, ENT_QUOTES, 'UTF-8') . '</p>' : '';
    }

    public function isActiveForm(string $formName): string
    {
        return $formName === $this->activeForm ? 'active' : '';
    }
}

?>