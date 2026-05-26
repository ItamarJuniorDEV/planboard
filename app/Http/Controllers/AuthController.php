<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = (new Timebox)->call(function () use ($validated) {
            $candidate = User::where('email', $validated['email'])->first();

            return ($candidate && Hash::check($validated['password'], $candidate->password))
                ? $candidate
                : null;
        }, 500_000);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas!',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => new UserResource($user),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso!',
        ], 200);
    }
}
