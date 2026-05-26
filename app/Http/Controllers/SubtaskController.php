<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subtask\BulkSubtaskRequest;
use App\Http\Requests\Subtask\IndexSubtaskRequest;
use App\Http\Requests\Subtask\StoreSubtaskRequest;
use App\Http\Requests\Subtask\UpdateSubtaskRequest;
use App\Http\Resources\SubtaskResource;
use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function index(IndexSubtaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $perPage = $validate['per_page'] ?? 50;

        $subTasks = $task->subtasks()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Subtarefas listadas com sucesso!',
            'data' => SubtaskResource::collection($subTasks)->resource,
        ], 200);
    }

    public function show(Project $project, Task $task, Subtask $subtask)
    {
        $this->authorize('view', $subtask);

        return response()->json([
            'success' => true,
            'message' => 'Subtask encontrada!',
            'data' => new SubtaskResource($subtask),
        ], 200);
    }

    public function store(StoreSubtaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $subtask = new Subtask;
        $subtask->task_id = $task->id;
        $subtask->user_id = $request->user()->id;
        $subtask->title = $validate['title'];
        $subtask->done = $validate['done'];
        $subtask->save();

        return response()->json([
            'success' => true,
            'message' => 'Subtarefa criada com sucesso!',
            'data' => new SubtaskResource($subtask),
        ], 201);
    }

    public function update(UpdateSubtaskRequest $request, Project $project, Task $task, Subtask $subtask)
    {
        $validate = $request->validated();

        $subtask->title = $validate['title'];
        $subtask->done = $validate['done'];
        $subtask->save();

        return response()->json([
            'success' => true,
            'message' => 'Subtarefa atualizada com sucesso!',
            'data' => new SubtaskResource($subtask),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Task $task, Subtask $subtask)
    {
        $this->authorize('delete', $subtask);

        $subtask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subtarefa excluída com sucesso!',
            'data' => new SubtaskResource($subtask),
        ], 200);
    }

    public function bulkComplete(BulkSubtaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $subtaskIds = $validate['subtask_ids'];

        $foundIds = $task->subtasks()
            ->whereIn('id', $subtaskIds)
            ->pluck('id')
            ->all();

        $notFound = array_values(array_diff($subtaskIds, $foundIds));

        $completed = 0;

        if (count($foundIds) > 0) {
            $completed = $task->subtasks()
                ->whereIn('id', $foundIds)
                ->update(['done' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Operação concluída!',
            'completed' => $completed,
            'not_found' => $notFound,
        ], 200);
    }

    public function bulkDelete(BulkSubtaskRequest $request, Project $project, Task $task)
    {
        $validate = $request->validated();

        $subtaskIds = $validate['subtask_ids'];

        $foundIds = $task->subtasks()
            ->whereIn('id', $subtaskIds)
            ->pluck('id')
            ->all();

        $notFound = array_values(array_diff($subtaskIds, $foundIds));

        $deleted = 0;

        if (count($foundIds) > 0) {
            $deleted = $task->subtasks()
                ->whereIn('id', $foundIds)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Operação concluída!',
            'deleted' => $deleted,
            'not_found' => $notFound,
        ], 200);
    }
}
