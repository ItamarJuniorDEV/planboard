<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 10;

        $users = User::paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Usuários listados com sucesso!',
            'data' => UserResource::collection($users)->resource,
        ], 200);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'message' => 'Usuário encontrado!',
            'data' => new UserResource($user),
        ], 200);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $created = new User;
        $created->name = $validated['name'];
        $created->email = $validated['email'];
        $created->password = Hash::make($validated['password']);
        $created->role = $validated['role'] ?? 'member';
        $created->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'data' => new UserResource($created),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

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
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!',
            'data' => new UserResource($user),
        ], 200);
    }
}
