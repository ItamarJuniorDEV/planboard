<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class ColumnController extends Controller
{
    public function index(Request $request, Project $project, Board $board)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 50;

        try {
            $columns = $board->columns()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Colunas listadas com sucesso!',
                'data' => $columns,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar colunas!',
            ], 500);
        }
    }

    public function show(Project $project, Board $board, Column $column)
    {
        return response()->json([
            'success' => true,
            'message' => 'Coluna encontrada com sucesso!',
            'data' => $column,
        ], 200);
    }

    public function store(Request $request, Project $project, Board $board)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $column = new Column();
            $column->board_id = $board->id;
            $column->user_id = $request->user()->id;
            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'success' => true,
                'message' => 'Coluna criada com sucesso!',
                'data' => $column,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar criar coluna!',
            ], 500);
        }
    }

    public function update(Request $request, Project $project, Board $board, Column $column)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'min:1'],
        ]);

        try {
            if ($column->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'success' => true,
                'message' => 'Coluna atualizada com sucesso!',
                'data' => $column,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar coluna!',
            ], 500);
        }
    }

    public function destroy(Request $request, Project $project, Board $board, Column $column)
    {
        try {
            if ($column->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $deletedColumn = $column->replicate();
            $deletedColumn->id = $column->id;
            $column->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coluna deletada com sucesso!',
                'data' => $deletedColumn,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar deletar coluna!',
            ], 500);
        }
    }
}
