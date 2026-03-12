<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class CommentController extends Controller
{
    public function index(Request $request, int $projectId, int $taskId)
    {
        $validate = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ]);

        try {
            $perPage = $validate['per_page'] ?? 50;

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

            $comments = $task->comments()->paginate($perPage);
            return response()->json([
                'success' => true,
                'message' => 'Comentários listados com sucesso!',
                'data' => $comments,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar comentários!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId, int $taskId)
    {
        $validate = $request->validate([
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:100'],
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

            $comment = new Comment();
            $comment->task_id = $task->id;
            $comment->user_id = $request->user()->id;
            $comment->content = $validate['content'];
            $comment->author = $validate['author'];
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comentário criado com sucesso!',
                'data' => $comment,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar adicionar comentário!',
            ], 500);
        }
    }

    public function show(int $projectId, int $taskId, int $id)
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

            $comment = $task->comments()->find($id);
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentário não encontrado!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comentário encontrado!',
                'data' => $comment,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar comentário!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $taskId, int $id)
    {
        $validate = $request->validate([
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:100'],
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

            $comment = $task->comments()->find($id);
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentário não encontrado!',
                ], 404);
            }

            if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $comment->content = $validate['content'];
            $comment->author = $validate['author'];
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comentário atualizado com sucesso!',
                'data' => $comment,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar atualizar comentário!',
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

            $comment = $task->comments()->find($id);
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentário não encontrado!',
                ], 404);
            }

            if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comentário excluído com sucesso!',
                'data' => $comment,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir comentário!',
            ], 500);
        }
    }

    public function bulkDelete(Request $request, int $projectId, int $taskId)
    {
        $validated = $request->validate([
            'comment_ids' => ['array', 'required', 'min:1'],
            'comment_ids.*' => ['integer', 'required'],
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

            $comments = $task->comments()
                ->whereIn('id', $validated['comment_ids'])
                ->get();

            $foundIds = [];
            foreach ($comments as $comment) {
                $foundIds[] = $comment->id;
            }

            $foundIdsMap = [];
            foreach ($foundIds as $found) {
                $foundIdsMap[$found] = true;
            }

            $notFound = [];
            foreach ($validated['comment_ids'] as $commentId) {
                if (!isset($foundIdsMap[$commentId])) {
                    $notFound[] = $commentId;
                }
            }

            $task->comments()->whereIn('id', $foundIds)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Operação concluída!',
                'deleted' => count($foundIds),
                'not_found' => $notFound,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir comentários!',
            ], 500);
        }
    }
}
