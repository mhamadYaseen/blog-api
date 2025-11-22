<?php

namespace Modules\Users\App\Actions;

use Modules\Users\App\Models\User;
use Modules\Users\App\Services\AuthService;

class LogoutAction
{
    public function __construct(private AuthService $authService) {}

    public function handle(User $user): bool
    {
        return $this->authService->logout($user);
    }
}
