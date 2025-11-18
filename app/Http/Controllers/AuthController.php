<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\LoginData;
use App\DTOs\RegisterUserData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $dto = new RegisterUserData(
            name: $request->validated()['name'],
            email: $request->validated()['email'],
            password: $request->validated()['password']
        );

        $result = $action($dto);
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
        $dto = new LoginData(
            email: $request->validated()['email'],
            password: $request->validated()['password']
        );

        $result = $action($dto);

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
        $action($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
