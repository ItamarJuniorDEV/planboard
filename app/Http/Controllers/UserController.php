<?php

namespace App\Http\Controllers;

use App\Models\User;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'Usuário encontrado!',
            'data' => $user,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['string', 'required', 'max:255'],
            'email' => ['string', 'required', 'email', 'unique:users,email'],
            'password' => ['string', 'required', 'min:8'],
            'role' => ['string', 'nullable', 'in:admin,member'],
        ]);

        try {
            $created = new User();
            $created->name = $validated['name'];
            $created->email = $validated['email'];
            $created->password = Hash::make($validated['password']);
            $created->role = $validated['role'] ?? 'member';
            $created->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'data' => $created,
            ], 201);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar usuário!',
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['string', 'required', 'max:255'],
            'email' => ['string', 'required','email', 'unique:users,email,' . $user->id],
            'password' => ['string', 'nullable', 'min:8'],
            'role' => ['string', 'nullable', 'in:admin,member'],
        ]);

        try {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'] ?? $user->role;

            if (isset($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!',
                'data' => $user,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar editar usuário!',
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!',
                'data' => $user,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir usuário!',
            ], 500);
        }
    }
}
