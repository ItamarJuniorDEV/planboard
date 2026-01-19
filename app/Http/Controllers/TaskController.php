<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Throwable;

class TaskController extends Controller
{
    public function index(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:todo,doing,done'],
        ]);

        $perPage = $validate['per_page'] ?? 10;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $query = $project->tasks()->select([
                'id',
                'project_id',
                'column_id',
                'title',
                'description',
                'priority',
                'status',
            ]);

            if (isset($validate['priority'])) {
                $query->where('priority', $validate['priority']);
            }

            if (isset($validate['search'])) {
                $query->where('title', 'like', '%' . $validate['search'] . '%');
            }

            if (isset($validate['status'])) {
                $query->where('status', $validate['status']);
            }

            $tasks = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Tarefas listadas com sucesso!',
                'data' => $tasks,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar as tarefas!',
            ], 500);
        }
    }

    public function show(int $projectId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tarefa encontrada com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao buscar tarefa!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = new Task();
            $task->project_id = $project->id;
            $task->title = $validate['title'];
            $task->description = $validate['description'] ?? null;
            $task->priority = $validate['priority'];
            $task->status = $validate['status'];
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Tarefa criada com sucesso!',
                'data' => $task,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao criar tarefa!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $id)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $task->title = $validate['title'];
            $task->description = $validate['description'] ?? null;
            $task->priority = $validate['priority'];
            $task->status = $validate['status'];
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Tarefa atualizada com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao atualizar tarefa!',
            ], 500);
        }
    }

    public function destroy(int $projectId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $deletedTask = $task;
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarefa excluída com sucesso!',
                'data' => $deletedTask,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao excluir tarefa!',
            ], 500);
        }
    }

    public function moveToColumn(int $projectId, int $boardId, int $columnId, int $taskId)
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

            $column = $board->columns()->find($columnId);

            if (!$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $task->column_id = $column->id;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Tarefa movida com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar mover tarefa!',
            ], 500);
        }
    }

    public function bulkMove(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer'],
            'column_id' => ['required', 'integer'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $column = Column::find($validate['column_id']);

            if (!$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $board = $project->boards()->find($column->board_id);

            if (!$board) {
                return response()->json([
                    'success' => false,
                    'message' => 'Essa coluna não pertence a este projeto!',
                ], 404);
            }

            $found = $project->tasks()->whereIn('id', $validate['task_ids'])->pluck('id')->toArray();
            $notFound = array_values(array_diff($validate['task_ids'], $found));

            $project->tasks()->whereIn('id', $found)->update(['column_id' => $column->id]);

            return response()->json([
                'success' => true,
                'message' => 'Operação concluída!',
                'moved' => count($found),
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao mover tarefas!',
            ], 500);
        }
    }

    public function bulkDelete(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $found = $project->tasks()->whereIn('id', $validate['task_ids'])->pluck('id')->toArray();
            $notFound = array_values(array_diff($validate['task_ids'], $found));

            $project->tasks()->whereIn('id', $found)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Operação concluída!',
                'deleted' => count($found),
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao deletar tarefas!',
            ], 500);
        }
    }
}
