<?php

namespace App\Actions\Auth;

use App\DTOs\LoginData;
use App\Services\AuthService;

class LoginAction
{
    public function __construct(private AuthService $authService) {}

    public function __invoke(LoginData $data): ?array
    {
        return $this->authService->login($data->toArray());
    }
}
