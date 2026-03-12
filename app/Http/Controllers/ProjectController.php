<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $validate = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'search' => ['nullable', 'string'],
            'deadline_from' => ['nullable', 'date'],
            'deadline_to' => ['nullable', 'date'],
            'order_by' => ['nullable', 'string', 'in:created_at,title,deadline,budget'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $perPage = $validate['per_page'] ?? 10;
        $orderBy = $validate['order_by'] ?? 'created_at';
        $direction = $validate['direction'] ?? 'desc';

        try {
            $query = Project::select([
                'id',
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
                $query->where('title', 'like', '%' . $validate['search'] . '%');
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
                'data' => $projects,
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar listar os projetos!',
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $project = Project::select(['id', 'title', 'description', 'budget', 'status', 'deadline'])
                ->find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Projeto encontrado com sucesso!',
                'data' => $project,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor ao tentar buscar o projeto!',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'deadline' => ['nullable', 'date'],
        ]);

        try {
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
                'data' => $project,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível criar o projeto!',
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $validate = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'deadline' => ['nullable', 'date'],
        ]);

        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            if ($project->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $project->title = $validate['title'];
            $project->description = $validate['description'] ?? null;
            $project->budget = $validate['budget'];
            $project->status = $validate['status'];
            $project->deadline = $validate['deadline'] ?? null;
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Projeto atualizado com sucesso!',
                'data' => $project,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor!',
            ], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

            if ($project->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação não autorizada!',
                ], 403);
            }

            $deletedProject = $project;
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projeto excluído com sucesso!',
                'data' => $deletedProject,
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor!',
            ], 500);
        }
    }

    public function stats(int $projectId)
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado!',
                ], 404);
            }

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
            $doneSubtasks  = $subtasks->sum('subtasks_done_count');

            $totalMilestones   = $project->milestones()->count();
            $overdueMilestones = $project->milestones()
                ->whereDate('due_date', '<', now())
                ->count();

            return response()->json([
                'success' => true,
                'tasks' => [
                    'by_status'   => $tasksByStatus,
                    'by_priority' => $tasksByPriority,
                ],
                'subtasks' => [
                    'total'   => $totalSubtasks,
                    'done'    => $doneSubtasks,
                    'pending' => $totalSubtasks - $doneSubtasks,
                ],
                'milestones' => [
                    'total'   => $totalMilestones,
                    'overdue' => $overdueMilestones,
                ],
            ], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor!',
            ], 500);
        }
    }
}
