<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 10;

        try {
            $users = User::paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Usuários listados com sucesso!',
                'data' => UserResource::collection($users)->resource,
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
                'data' => new UserResource($user),
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar usuário!',
            ], 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        try {
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->role = $validated['role'] ?? 'member';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'data' => new UserResource($user),
            ], 201);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar usuário!',
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $validated = $request->validated();

        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado!',
                ], 404);
            }

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
                'data' => new UserResource($user),
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar editar usuário!',
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado!',
                ], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!',
                'data' => new UserResource($user),

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
