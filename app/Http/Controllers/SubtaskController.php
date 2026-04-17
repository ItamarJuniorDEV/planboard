<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Throwable;

class SubtaskController extends Controller
{
    public function index(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ]);

        $perPage = $validate['per_page'] ?? 50;

        try {
            $subTasks = $task->subtasks()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Subtarefas listadas com sucesso!',
                'data' => $subTasks,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao listar SubTasks!',
            ], 500);
        }
    }

    public function show(Project $project, Task $task, Subtask $subtask)
    {
        return response()->json([
            'success' => true,
            'message' => 'Subtask encontrada!',
            'data' => $subtask,
        ], 200);
    }

    public function store(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ]);

        try {
            $subtask = new Subtask();
            $subtask->task_id = $task->id;
            $subtask->user_id = $request->user()->id;
            $subtask->title = $validate['title'];
            $subtask->done = $validate['done'];
            $subtask->save();

            return response()->json([
                'success' => true,
                'message' => 'Subtarefa criada com sucesso!',
                'data' => $subtask,
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar SubTarefa!',
            ], 500);
        }
    }

    public function update(Request $request, Project $project, Task $task, Subtask $subtask)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ]);

        try {
            if ($subtask->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $subtask->title = $validate['title'];
            $subtask->done = $validate['done'];
            $subtask->save();

            return response()->json([
                'success' => true,
                'message' => 'Subtarefa atualizada com sucesso!',
                'data' => $subtask,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar atualizar a subtarefa!',
            ], 500);
        }
    }

    public function destroy(Request $request, Project $project, Task $task, Subtask $subtask)
    {
        try {
            if ($subtask->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $subtask->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subtarefa excluída com sucesso!',
                'data' => $subtask,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar excluir subtarefa!',
            ], 500);
        }
    }

    public function bulkComplete(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'subtask_ids' => ['required', 'array', 'min:1'],
            'subtask_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        try {
            $subtaskIds = $validate['subtask_ids'];

            $foundIds = $task->subtasks()
                ->whereIn('id', $subtaskIds)
                ->pluck('id')
                ->all();

            $notFound = array_values(array_diff($subtaskIds, $foundIds));

            $completed = 0;

            if (count($foundIds) > 0) {
                $completed = $task->subtasks()
                    ->whereIn('id', $foundIds)
                    ->update(['done' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Operação concluída!',
                'completed' => $completed,
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar concluir subtarefas em lote!',
            ], 500);
        }
    }

    public function bulkDelete(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'subtask_ids' => ['required', 'array', 'min:1'],
            'subtask_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        try {
            $subtaskIds = $validate['subtask_ids'];

            $foundIds = $task->subtasks()
                ->whereIn('id', $subtaskIds)
                ->pluck('id')
                ->all();

            $notFound = array_values(array_diff($subtaskIds, $foundIds));

            $deleted = 0;

            if (count($foundIds) > 0) {
                $deleted = $task->subtasks()
                    ->whereIn('id', $foundIds)
                    ->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Operação concluída!',
                'deleted' => $deleted,
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar excluir subtarefas em lote!',
            ], 500);
        }
    }
}
