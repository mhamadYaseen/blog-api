<?php

namespace Modules\Users\Controllers;

use Modules\Users\Actions\LoginAction;
use Modules\Users\Actions\LogoutAction;
use Modules\Users\Actions\RegisterUserAction;
use Modules\Users\Requests\LoginRequest;
use Modules\Users\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $validated = $request->validated();

        $result = $action->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password']
        );

        $user = $result['user'];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $validated = $request->validated();

        $result = $action->handle(
            email: $validated['email'],
            password: $validated['password']
        );

        if (!$result) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], 401);
        }

        $user = $result['user'];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $result['token'],
        ], 200);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->handle($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
