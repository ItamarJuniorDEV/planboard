<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class MilestoneController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $validated = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:20'],
            'search' => ['nullable', 'string'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
            'order_by' => ['nullable', 'string', 'in:created_at,due_date,title'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $orderBy = $validated['order_by'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'asc';
        $perPage = $validated['per_page'] ?? 20;

        try {
            $query = $project->milestones();

            if (isset($validated['search'])) {
                $query->where('title', 'LIKE', '%' . $validated['search'] . '%');
            }

            if (isset($validated['due_from'])) {
                $query->whereDate('due_date', '>=', $validated['due_from']);
            }

            if (isset($validated['due_to'])) {
                $query->whereDate('due_date', '<=', $validated['due_to']);
            }

            $query->orderBy($orderBy, $direction);

            $milestones = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Marcos listados com sucesso!',
                'data' => $milestones,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar os marcos!',
            ], 500);
        }
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
        ]);

        try {
            $milestone = new Milestone();
            $milestone->project_id = $project->id;
            $milestone->user_id = $request->user()->id;
            $milestone->title = $validated['title'];
            $milestone->due_date = $validated['due_date'];
            $milestone->save();

            return response()->json([
                'success' => true,
                'message' => 'Marco criado com sucesso!',
                'data' => $milestone,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar novo marco!',
            ], 500);
        }
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
        ]);

        try {
            if ($milestone->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $milestone->title = $validated['title'];
            $milestone->due_date = $validated['due_date'];
            $milestone->save();

            return response()->json([
                'success' => true,
                'message' => 'Marco atualizado com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar o marco!',
            ], 500);
        }
    }

    public function show(Project $project, Milestone $milestone)
    {
        return response()->json([
            'success' => true,
            'message' => 'Marco encontrado com sucesso!',
            'data' => $milestone,
        ], 200);
    }

    public function destroy(Request $request, Project $project, Milestone $milestone)
    {
        try {
            if ($milestone->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $milestone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Marco excluído com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar deletar marco!',
            ], 500);
        }
    }

    public function bulkDelete(Request $request, Project $project)
    {
        $validated = $request->validate([
            'milestone_ids' => ['array', 'required', 'min:1'],
            'milestone_ids.*' => ['integer', 'required'],
        ]);

        try {
            $foundIds = $project->milestones()
                ->whereIn('id', $validated['milestone_ids'])
                ->pluck('id')
                ->all();

            $notFound = array_values(array_diff($validated['milestone_ids'], $foundIds));

            $project->milestones()->whereIn('id', $foundIds)->delete();

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
                'message' => 'Erro interno no servidor ao tentar excluir marcos!',
            ], 500);
        }
    }
}
