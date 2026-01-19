<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Throwable;

class SubtaskController extends Controller
{
    public function index(Request $request, int $projectId, int $taskId)
    {
        $validate = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ]);

        $perPage = $validate['per_page'] ?? 50;

        try {

            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

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

    public function show(Request $request, int $projectId, int $taskId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $subTask = $task->subtasks()->find($id);

            if (!$subTask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subtarefa não encontrada!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subtask encontrada!',
                'data' => $subTask,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar Sub Tarefa!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId, int $taskId)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ]);

        try {

            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $subtask = new Subtask();
            $subtask->task_id = $task->id;
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

    public function update(Request $request, int $projectId, int $taskId, int $id)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ]);
        try {
            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $subtask = $task->subtasks()->find($id);
            if (!$subtask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subtarefa não encontrada!',
                ], 404);
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



    public function destroy(Request $request, int $projectId, int $taskId, int $id)
    {
        try {
            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $task = $project->tasks()->find($taskId);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada!',
                ], 404);
            }

            $subtask = $task->subtasks()->find($id);
            if (!$subtask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subtarefa não encontrada!',
                ], 404);
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
}
