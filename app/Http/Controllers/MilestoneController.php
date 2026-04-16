<?php

namespace App\Http\Controllers;

use App\Http\Requests\Milestone\IndexMilestoneRequest;
use App\Http\Requests\Milestone\StoreMilestoneRequest;
use App\Http\Requests\Milestone\UpdateMilestoneRequest;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class MilestoneController extends Controller
{
    public function index(IndexMilestoneRequest $request, int $projectId)
    {
        $validated = $request->validated();
        $orderBy = $validated['order_by'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'asc';
        $perPage = $validated['per_page'] ?? 20;

        try {
            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }
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

    public function store(StoreMilestoneRequest $request, int $projectId)
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

    public function update(UpdateMilestoneRequest $request, int $projectId, int $id)
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

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

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

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Marco encontrado com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar marco!',
            ], 500);
        }
    }

    public function destroy(Request $request, int $projectId, int $id)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

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

    public function bulkDelete(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'milestone_ids' => ['array', 'required', 'min:1'],
            'milestone_ids.*' => ['integer', 'required'],
        ]);

        try {
            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestones = $project->milestones()
                ->whereIn('id', $validated['milestone_ids'])
                ->get();

            $foundIds = [];
            foreach ($milestones as $milestone) {
                $foundIds[] = $milestone->id;
            }

            $notFound = [];
            foreach ($validated['milestone_ids'] as $milestoneId) {
                if (!in_array($milestoneId, $foundIds)) {
                    $notFound[] = $milestoneId;
                }
            }

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
