<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class ColumnController extends Controller
{
    public function index(Request $request, int $projectId, int $boardId)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 50;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $columns = $board->columns()->paginate($perPage);

            return response()->json([
                'message' => 'Colunas listadas com sucesso!',
                'data' => $columns,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao tentar listar colunas!',
            ], 500);
        }
    }

    public function show(int $projectId, int $boardId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            return response()->json([
                'message' => 'Coluna encontrada com sucesso!',
                'data' => $column,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId, int $boardId)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = new Column();
            $column->board_id = $board->id;
            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'message' => 'Coluna criada com sucesso!',
                'data' => $column,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno ao tentar criar coluna!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $boardId, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'message' => 'Coluna atualizada com sucesso!',
                'data' => $column,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao tentar atualizar coluna!',
            ], 500);
        }
    }

    public function destroy(int $projectId, int $boardId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $column->delete();

            return response()->json([
                'message' => 'Coluna deletada com sucesso!',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao tentar deletar coluna!',
            ], 500);
        }
    }
}
