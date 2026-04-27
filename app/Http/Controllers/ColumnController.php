<?php

namespace App\Http\Controllers;

use App\Http\Requests\Column\IndexColumnRequest;
use App\Http\Requests\Column\StoreColumnRequest;
use App\Http\Requests\Column\UpdateColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Column;
use App\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Throwable;

class ColumnController extends Controller
{
    public function index(IndexColumnRequest $request, int $projectId, int $boardId)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 50;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $columns = $board->columns()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Colunas listadas com sucesso!',
                'data' => ColumnResource::collection($columns)->resource,
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
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
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Coluna encontrada com sucesso!',
                'data' => new ColumnResource($column),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor!',
            ], 500);
        }
    }

    public function store(StoreColumnRequest $request, int $projectId, int $boardId)
    {
        $validated = $request->validated();

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = new Column();
            $column->board_id = $board->id;
            $column->user_id = $request->user()->id;
            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'success' => true,
                'message' => 'Coluna criada com sucesso!',
                'data' => new ColumnResource($column),
            ], 201);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar criar coluna!',
            ], 500);
        }
    }

    public function update(UpdateColumnRequest $request, int $projectId, int $boardId, int $id)
    {
        $validated = $request->validated();

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $this->authorize('update', $column);

            $column->name = $validated['name'];
            $column->position = $validated['position'];
            $column->save();

            return response()->json([
                'success' => true,
                'message' => 'Coluna atualizada com sucesso!',
                'data' => new ColumnResource($column),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar coluna!',
            ], 500);
        }
    }

    public function destroy(Request $request, int $projectId, int $boardId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($id);

            if (!$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $this->authorize('delete', $column);

            $deletedColumn = $column;
            $column->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coluna deletada com sucesso!',
                'data' => new ColumnResource($deletedColumn),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar deletar coluna!',
            ], 500);
        }
    }
}
