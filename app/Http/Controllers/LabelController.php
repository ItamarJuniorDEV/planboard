<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class LabelController extends Controller
{
    public function index(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $perPage = $validated['per_page'] ?? 30;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $labels = $project->labels()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Etiquetas listadas com sucesso!',
                'data' => $labels,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar as etiquetas!',
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

            $label = $project->labels()->find($id);

            if (!$label) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etiqueta não encontrada!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta encontrada com sucesso!',
                'data' => $label,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar etiqueta!',
            ], 500);
        }
    }

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $label = new Label();
            $label->project_id = $project->id;
            $label->name = $validated['name'];
            $label->color = $validated['color'];
            $label->save();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta criada com sucesso!',
                'data' => $label,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar etiqueta!',
            ], 500);
        }
    }

    public function update(Request $request, int $projectId, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
        ]);

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $label = $project->labels()->find($id);

            if (!$label) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etiqueta não encontrada!',
                ], 404);
            }

            $label->name = $validated['name'];
            $label->color = $validated['color'];
            $label->save();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta atualizada com sucesso!',
                'data' => $label,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar etiqueta!',
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

            $label = $project->labels()->find($id);

            if (!$label) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etiqueta não encontrada!',
                ], 404);
            }

            $label->delete();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta excluída com sucesso!',
                'data' => $label,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir etiqueta!',
            ], 500);
        }
    }
}
