<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\BulkDeleteTaskRequest;
use App\Http\Requests\Task\BulkMoveTaskRequest;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(IndexTaskRequest $request, Project $project)
    {
        $validate = $request->validated();

        $perPage = $validate['per_page'] ?? 10;
        $orderBy = $validate['order_by'] ?? 'created_at';
        $direction = $validate['direction'] ?? 'asc';

        $query = $project->tasks()->select([
            'id',
            'user_id',
            'project_id',
            'column_id',
            'title',
            'description',
            'priority',
            'status',
        ]);

        if (isset($validate['priority'])) {
            $query->where('priority', $validate['priority']);
        }

        if (isset($validate['search'])) {
            $query->where('title', 'like', '%'.$validate['search'].'%');
        }

        if (isset($validate['status'])) {
            $query->where('status', $validate['status']);
        }

        $query->orderBy($orderBy, $direction);
        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Tarefas listadas com sucesso!',
            'data' => TaskResource::collection($tasks)->resource,
        ], 200);
    }

    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        return response()->json([
            'success' => true,
            'message' => 'Tarefa encontrada com sucesso!',
            'data' => new TaskResource($task),
        ], 200);
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $validate = $request->validated();

        $task = new Task;
        $task->project_id = $project->id;
        $task->user_id = $request->user()->id;
        $task->title = $validate['title'];
        $task->description = $validate['description'] ?? null;
        $task->priority = $validate['priority'];
        $task->status = $validate['status'];
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Tarefa criada com sucesso!',
            'data' => new TaskResource($task),
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $task->title = $validate['title'];
        $task->description = $validate['description'] ?? null;
        $task->priority = $validate['priority'];
        $task->status = $validate['status'];
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Tarefa atualizada com sucesso!',
            'data' => new TaskResource($task),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Task $task)
    {
        $this->authorize('delete', $task);

        $deletedTask = $task->replicate();
        $deletedTask->id = $task->id;
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tarefa excluída com sucesso!',
            'data' => new TaskResource($deletedTask),
        ], 200);
    }

    public function move(MoveTaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $column = Column::find($validate['column_id']);

        $board = $project->boards()->find($column->board_id);

        if (! $board) {
            return response()->json([
                'success' => false,
                'message' => 'Essa coluna não pertence a este projeto!',
            ], 404);
        }

        $task->column_id = $column->id;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Tarefa movida com sucesso!',
            'data' => new TaskResource($task),
        ], 200);
    }

    public function bulkMove(BulkMoveTaskRequest $request, Project $project)
    {
        $validate = $request->validated();

        $column = Column::find($validate['column_id']);

        if (! $column) {
            return response()->json([
                'success' => false,
                'message' => 'Coluna não encontrada!',
            ], 404);
        }

        $board = $project->boards()->find($column->board_id);

        if (! $board) {
            return response()->json([
                'success' => false,
                'message' => 'Essa coluna não pertence a este projeto!',
            ], 404);
        }

        $tasks = $project->tasks()->whereIn('id', $validate['task_ids'])->get();

        $foundIds = $tasks->pluck('id')->all();

        $notFound = array_values(array_diff($validate['task_ids'], $foundIds));

        $project->tasks()->whereIn('id', $foundIds)->update(['column_id' => $column->id]);

        return response()->json([
            'success' => true,
            'message' => 'Operação concluída!',
            'moved' => count($foundIds),
            'not_found' => $notFound,
        ], 200);
    }

    public function bulkDelete(BulkDeleteTaskRequest $request, Project $project)
    {
        $validate = $request->validated();

        $tasks = $project->tasks()->whereIn('id', $validate['task_ids'])->get();

        $foundIds = $tasks->pluck('id')->all();

        $notFound = array_values(array_diff($validate['task_ids'], $foundIds));

        $project->tasks()->whereIn('id', $foundIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Operação concluída!',
            'deleted' => count($foundIds),
            'not_found' => $notFound,
        ], 200);
    }
}
