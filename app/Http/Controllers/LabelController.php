<?php

namespace App\Http\Controllers;

use App\Http\Requests\Label\IndexLabelRequest;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Label\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Throwable;

class LabelController extends Controller
{
    public function index(IndexLabelRequest $request, int $projectId)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 10;

        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            $query = $project->labels();

            if (isset($validated['search'])) {
                $query->where('name', 'LIKE', '%' . $validated['search'] . '%');
            }

            $labels = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Etiquetas listadas com sucesso!',
                'data' => LabelResource::collection($labels)->resource,
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
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
                'data' => new LabelResource($label),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar etiqueta!',
            ], 500);
        }
    }

    public function store(StoreLabelRequest $request, int $projectId)
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

            $label = new Label();
            $label->project_id = $project->id;
            $label->user_id = $request->user()->id;
            $label->name = $validated['name'];
            $label->color = $validated['color'];
            $label->save();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta criada com sucesso!',
                'data' => new LabelResource($label),
            ], 201);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar criar etiqueta!',
            ], 500);
        }
    }

    public function update(UpdateLabelRequest $request, int $projectId, int $id)
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

            $label = $project->labels()->find($id);

            if (!$label) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etiqueta não encontrada!',
                ], 404);
            }

            $this->authorize('update', $label);

            $label->name = $validated['name'];
            $label->color = $validated['color'];
            $label->save();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta atualizada com sucesso!',
                'data' => new LabelResource($label),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar atualizar etiqueta!',
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

            $label = $project->labels()->find($id);

            if (!$label) {
                return response()->json([
                    'success' => false,
                    'message' => 'Etiqueta não encontrada!',
                ], 404);
            }

            $this->authorize('delete', $label);

            $label->delete();

            return response()->json([
                'success' => true,
                'message' => 'Etiqueta excluída com sucesso!',
                'data' => new LabelResource($label),
            ], 200);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar excluir etiqueta!',
            ], 500);
        }
    }
}
