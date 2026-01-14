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
        ]);

        $perPage = $validated['per_page'] ?? 20;

        try {
            $project = Project::find($projectId);
            if (!$project) {
                return response()->json([
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }
            $milestones = $project->milestones()->paginate($perPage);
            return response()->json([
                'message' => 'Marcos listados com sucesso!',
                'data' => $milestones,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestone = new Milestone();
            $milestone->project_id = $project->id;
            $milestone->title = $validated['title'];
            $milestone->due_date = $validated['due_date'];
            $milestone->save();

            return response()->json([
                'message' => 'Marco criado com sucesso!',
                'data' => $milestone,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

            $milestone->title = $validated['title'];
            $milestone->due_date = $validated['due_date'];
            $milestone->save();

            return response()->json([
                'message' => 'Marco atualizado com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

            return response()->json([
                'message' => 'Marco encontrado com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
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
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $milestone = $project->milestones()->find($id);

            if (!$milestone) {
                return response()->json([
                    'message' => 'Marco não encontrado!',
                ], 404);
            }

            $milestone->delete();

            return response()->json([
                'message' => 'Marco excluído com sucesso!',
                'data' => $milestone,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Erro interno no servidor ao tentar deletar marco!',
            ], 500);
        }
    }
}
