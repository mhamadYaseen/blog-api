<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class RegisterUserAction
{
    public function __construct(private AuthService $authService) {}

    public function handle(string $name, string $email, string $password): array
    {
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];

        return $this->authService->register($data);
    }
}
