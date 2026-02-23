<?php

namespace App\Http\Controllers;

use App\Models\User;
use Throwable;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:10'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        try {
            $users = User::paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Usuários listados com sucesso!',
                'data' => $users,
            ], 200);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar usuários!',
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuário encontrado!',
                'data' => $user,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar usuário!',
            ], 500);
        }
    }

    public function store(Request $request, )
    {

    }
}
