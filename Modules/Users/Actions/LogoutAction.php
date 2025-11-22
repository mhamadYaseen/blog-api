<?php

namespace Modules\Users\Actions;

use Modules\Users\Models\User;
use Modules\Users\Services\AuthService;

class LogoutAction
{
    public function __construct(private AuthService $authService) {}

    public function handle(User $user): bool
    {
        return $this->authService->logout($user);
    }
}
