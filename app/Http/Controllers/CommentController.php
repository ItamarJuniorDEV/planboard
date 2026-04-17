<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Throwable;

class CommentController extends Controller
{
    public function index(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ]);

        $perPage = $validate['per_page'] ?? 50;

        try {
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

    public function store(Request $request, Project $project, Task $task)
    {
        $validate = $request->validate([
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:100'],
        ]);

        try {
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

    public function show(Project $project, Task $task, Comment $comment)
    {
        return response()->json([
            'success' => true,
            'message' => 'Comentário encontrado!',
            'data' => $comment,
        ], 200);
    }

    public function update(Request $request, Project $project, Task $task, Comment $comment)
    {
        $validate = $request->validate([
            'content' => ['required', 'string'],
            'author' => ['required', 'string', 'max:100'],
        ]);

        try {
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

    public function destroy(Request $request, Project $project, Task $task, Comment $comment)
    {
        try {
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

    public function bulkDelete(Request $request, Project $project, Task $task)
    {
        $validated = $request->validate([
            'comment_ids' => ['array', 'required', 'min:1'],
            'comment_ids.*' => ['integer', 'required'],
        ]);

        try {
            $foundIds = $task->comments()
                ->whereIn('id', $validated['comment_ids'])
                ->pluck('id')
                ->all();

            $notFound = array_values(array_diff($validated['comment_ids'], $foundIds));

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
