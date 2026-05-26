<?php

namespace App\Http\Controllers;

use App\Http\Requests\Milestone\IndexMilestoneRequest;
use App\Http\Requests\Milestone\StoreMilestoneRequest;
use App\Http\Requests\Milestone\UpdateMilestoneRequest;
use App\Http\Resources\MilestoneResource;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function index(IndexMilestoneRequest $request, Project $project)
    {
        $validated = $request->validated();

        $orderBy = $validated['order_by'] ?? 'created_at';
        $direction = $validated['direction'] ?? 'asc';
        $perPage = $validated['per_page'] ?? 20;

        $query = $project->milestones();

        if (isset($validated['search'])) {
            $query->where('title', 'LIKE', '%'.$validated['search'].'%');
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
            'data' => MilestoneResource::collection($milestones)->resource,
        ], 200);
    }

    public function store(StoreMilestoneRequest $request, Project $project)
    {
        $validated = $request->validated();

        $milestone = new Milestone;
        $milestone->project_id = $project->id;
        $milestone->user_id = $request->user()->id;
        $milestone->title = $validated['title'];
        $milestone->due_date = $validated['due_date'] ?? null;
        $milestone->save();

        return response()->json([
            'success' => true,
            'message' => 'Marco criado com sucesso!',
            'data' => new MilestoneResource($milestone),
        ], 201);
    }

    public function update(UpdateMilestoneRequest $request, Project $project, Milestone $milestone)
    {
        $validated = $request->validated();

        $milestone->title = $validated['title'];
        $milestone->due_date = $validated['due_date'] ?? null;
        $milestone->save();

        return response()->json([
            'success' => true,
            'message' => 'Marco atualizado com sucesso!',
            'data' => new MilestoneResource($milestone),
        ], 200);
    }

    public function show(Project $project, Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return response()->json([
            'success' => true,
            'message' => 'Marco encontrado com sucesso!',
            'data' => new MilestoneResource($milestone),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Milestone $milestone)
    {
        $this->authorize('delete', $milestone);

        $milestone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Marco excluído com sucesso!',
            'data' => new MilestoneResource($milestone),
        ], 200);
    }
}
