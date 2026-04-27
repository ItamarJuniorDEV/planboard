<?php

namespace App\Http\Controllers;

use App\Http\Requests\Label\IndexLabelRequest;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Label\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(IndexLabelRequest $request, Project $project)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 10;

        $query = $project->labels();

        if (isset($validated['search'])) {
            $query->where('name', 'LIKE', '%'.$validated['search'].'%');
        }

        $labels = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Etiquetas listadas com sucesso!',
            'data' => LabelResource::collection($labels)->resource,
        ], 200);
    }

    public function show(Project $project, Label $label)
    {
        $this->authorize('view', $label);

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta encontrada com sucesso!',
            'data' => new LabelResource($label),
        ], 200);
    }

    public function store(StoreLabelRequest $request, Project $project)
    {
        $validated = $request->validated();

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
    }

    public function update(UpdateLabelRequest $request, Project $project, Label $label)
    {
        $validated = $request->validated();

        $label->name = $validated['name'];
        $label->color = $validated['color'];
        $label->save();

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta atualizada com sucesso!',
            'data' => new LabelResource($label),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Label $label)
    {
        $this->authorize('delete', $label);

        $label->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etiqueta excluída com sucesso!',
            'data' => new LabelResource($label),
        ], 200);
    }
}
