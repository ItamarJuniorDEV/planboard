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
            'priority' => ['nullable', 'string', 'max:20'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:todo,doing,done'],
        ]);

        $perPage = $validate['per_page'] ?? 10;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
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
                'message' => 'Tarefas listadas com sucesso!',
                'data' => $tasks,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            return response()->json([
                'message' => 'Tarefa encontrada com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno ao buscar tarefa!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'max:20'],
            'completed' => ['required', 'boolean'],
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = new Task();
            $task->project_id = $project->id;
            $task->title = $validate['title'];
            $task->description = $validate['description'] ?? null;
            $task->priority = $validate['priority'];
            $task->completed = $validate['completed'];
            $task->status = $validate['status'];
            $task->save();

            return response()->json([
                'message' => 'Tarefa criada com sucesso!',
                'data' => $task,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao criar tarefa!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $id)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'max:20'],
            'completed' => ['required', 'boolean'],
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $task->title = $validate['title'];
            $task->description = $validate['description'] ?? null;
            $task->priority = $validate['priority'];
            $task->completed = $validate['completed'];
            $task->status = $validate['status'];
            $task->save();

            return response()->json([
                'message' => 'Tarefa atualizada com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $task->delete();

            return response()->json([
                'message' => 'Tarefa excluída com sucesso!',
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $board = $project->boards()->find($boardId);

            if (!$board) {
                return response()->json([
                    'message' => 'Quadro não encontrado!',
                ], 404);
            }

            $column = $board->columns()->find($columnId);

            if (!$column) {
                return response()->json([
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);

            if (!$task) {
                return response()->json([
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $task->column_id = $column->id;
            $task->save();

            return response()->json([
                'message' => 'Tarefa movida com sucesso!',
                'data' => $task,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $column = Column::find($validate['column_id']);

            if (!$column) {
                return response()->json([
                    'message' => 'Coluna não encontrada!',
                ], 404);
            }

            $board = $project->boards()->find($column->board_id);

            if (!$board) {
                return response()->json([
                    'message' => 'Essa coluna não pertence a este projeto!',
                ], 404);
            }

            $moved = 0;
            $notFound = [];

            foreach ($validate['task_ids'] as $taskId) {
                $task = $project->tasks()->find($taskId);

                if ($task) {
                    $task->column_id = $column->id;
                    $task->save();
                    $moved++;
                } else {
                    $notFound[] = $taskId;
                }
            }

            return response()->json([
                'message' => 'Operação concluída!',
                'moved' => $moved,
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $deleted = 0;
            $notFound = [];

            foreach ($validate['task_ids'] as $taskId) {
                $task = $project->tasks()->find($taskId);

                if ($task) {
                    $task->delete();
                    $deleted++;
                } else {
                    $notFound[] = $taskId;
                }
            }

            return response()->json([
                'message' => 'Operação concluída!',
                'deleted' => $deleted,
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao deletar tarefas!',
            ], 500);
        }
    }
}
