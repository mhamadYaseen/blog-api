<?php

namespace Modules\Users\App\Actions;

use Modules\Users\App\Services\AuthService;

class LoginAction
{
    public function __construct(private AuthService $authService) {}

    public function handle(string $email, string $password): ?array
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        return $this->authService->login($credentials);
    }
}
