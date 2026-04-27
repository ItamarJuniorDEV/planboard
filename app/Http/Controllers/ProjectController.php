<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(IndexProjectRequest $request)
    {
        $validate = $request->validated();

        $perPage = $validate['per_page'] ?? 10;
        $orderBy = $validate['order_by'] ?? 'created_at';
        $direction = $validate['direction'] ?? 'desc';

        $query = Project::select([
            'id',
            'user_id',
            'title',
            'description',
            'budget',
            'status',
            'deadline',
        ]);

        if (isset($validate['status'])) {
            $query->where('status', $validate['status']);
        }

        if (isset($validate['search'])) {
            $query->where('title', 'like', '%'.$validate['search'].'%');
        }

        if (isset($validate['deadline_from'])) {
            $query->whereDate('deadline', '>=', $validate['deadline_from']);
        }

        if (isset($validate['deadline_to'])) {
            $query->whereDate('deadline', '<=', $validate['deadline_to']);
        }

        $query->orderBy($orderBy, $direction);

        $projects = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Projetos listados com sucesso!',
            'data' => ProjectResource::collection($projects)->resource,
        ], 200);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return response()->json([
            'success' => true,
            'message' => 'Projeto encontrado com sucesso!',
            'data' => new ProjectResource($project),
        ], 200);
    }

    public function store(StoreProjectRequest $request)
    {
        $validate = $request->validated();

        $project = new Project();
        $project->title = $validate['title'];
        $project->description = $validate['description'] ?? null;
        $project->budget = $validate['budget'];
        $project->status = $validate['status'];
        $project->deadline = $validate['deadline'] ?? null;
        $project->user_id = $request->user()->id;
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Projeto criado com sucesso!',
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $validate = $request->validated();

        $project->title = $validate['title'];
        $project->description = $validate['description'] ?? null;
        $project->budget = $validate['budget'];
        $project->status = $validate['status'];
        $project->deadline = $validate['deadline'] ?? null;
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Projeto atualizado com sucesso!',
            'data' => new ProjectResource($project),
        ], 200);
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorize('delete', $project);

        $deletedProject = $project->replicate();
        $deletedProject->id = $project->id;
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projeto excluído com sucesso!',
            'data' => new ProjectResource($deletedProject),
        ], 200);
    }

    public function stats(Project $project)
    {
        $this->authorize('view', $project);

        $tasksByStatus = $project->tasks()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $tasksByPriority = $project->tasks()
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->get();

        $subtasks = $project->tasks()
            ->withCount([
                'subtasks',
                'subtasks as subtasks_done_count' => fn ($q) => $q->where('done', true),
            ])
            ->get();

        $totalSubtasks = $subtasks->sum('subtasks_count');
        $doneSubtasks = $subtasks->sum('subtasks_done_count');

        $totalMilestones = $project->milestones()->count();
        $overdueMilestones = $project->milestones()
            ->whereDate('due_date', '<', now())
            ->count();

        return response()->json([
            'success' => true,
            'tasks' => [
                'by_status' => $tasksByStatus,
                'by_priority' => $tasksByPriority,
            ],
            'subtasks' => [
                'total' => $totalSubtasks,
                'done' => $doneSubtasks,
                'pending' => $totalSubtasks - $doneSubtasks,
            ],
            'milestones' => [
                'total' => $totalMilestones,
                'overdue' => $overdueMilestones,
            ],
        ], 200);
    }
}
