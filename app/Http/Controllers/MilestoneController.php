<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class MilestoneController extends Controller
{
    public function index(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'per_page' => ['integer', 'nullable', 'min:1', 'max:20'],
            'search' => ['nullable', 'string'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
        ]);

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
                $query->where('due_date', '>=', $validated['due_from']);
            }

            if (isset($validated['due_to'])) {
                $query->where('due_date', '<=', $validated['due_to']);
            }

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

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['required', 'date'],
        ]);

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

    public function update(Request $request, int $projectId, int $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['required', 'date'],
        ]);

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

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marco não encontrado!',
                ], 404);
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
}
