<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuthService;

class LogoutAction
{
    public function __construct(private AuthService $authService) {}

    public function handle(User $user): bool
    {
        return $this->authService->logout($user);
    }
}
