<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class BoardController extends Controller
{
    public function index(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['nullable', 'string', 'in:active,archived'],
            'search' => ['nullable', 'string'],
        ]);

        $perPage = $validate['per_page'] ?? 20;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $query = $project->boards();

            if (isset($validate['status'])) {
                $query->where('status', $validate['status']);
            }

            if (isset($validate['search'])) {
                $query->where('name', 'LIKE', '%' . $validate['search'] . '%');
            }

            $boards = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Quadros listados com sucesso!',
                'data' => $boards,
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar os quadros!',
            ], 500);
        }
    }

    public function show(Request $request, int $projectId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($id);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quadro encontrado com sucesso!',
                'data' => $board,
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar encontrar o quadro!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,archived'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = new Board();
            $board->project_id = $project->id;
            $board->name = $validate['name'];
            $board->status = $validate['status'];
            $board->save();

            return response()->json([
                'success' => true,
                'message' => 'Quadro criado com sucesso!',
                'data' => $board,
            ], 201);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar o quadro!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $id)
    {
        $validate = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,archived'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($id);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $board->name = $validate['name'];
            $board->status = $validate['status'];
            $board->save();

            return response()->json([
                'success' => true,
                'message' => 'Quadro atualizado com sucesso!',
                'data' => $board,
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar o quadro!',
            ], 500);
        }
    }

    public function destroy(Request $request, int $projectId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($id);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $board->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quadro excluído com sucesso!',
                'data' => $board,
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir o quadro!',
            ], 500);
        }
    }
}
