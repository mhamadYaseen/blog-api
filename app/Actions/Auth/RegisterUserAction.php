<?php

namespace App\Actions\Auth;

use App\DTOs\RegisterUserData;
use App\Services\AuthService;

class RegisterUserAction
{
    public function __construct(private AuthService $authService) {}

    public function __invoke(RegisterUserData $data): array
    {
        return $this->authService->register($data->toArray());
    }
}
