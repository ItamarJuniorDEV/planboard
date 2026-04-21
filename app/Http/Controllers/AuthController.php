<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['string', 'required', 'email'],
            'password' => ['string', 'required'],
        ]);

        // Constant-time path to avoid leaking whether the email exists.
        $user = (new Timebox())->call(function () use ($validated) {
            $candidate = User::where('email', $validated['email'])->first();

            return ($candidate && Hash::check($validated['password'], $candidate->password))
                ? $candidate
                : null;
        }, 500_000);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas!',
            ], 401);
        }

        try {
            $token = $user->createToken('auth_token')->plainTextToken;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível concluir o login no momento.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso!',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar realizar logout!',
            ], 500);
        }
    }
}
