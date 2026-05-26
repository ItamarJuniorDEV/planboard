<?php

namespace App\Http\Controllers;

use App\Http\Requests\Column\IndexColumnRequest;
use App\Http\Requests\Column\StoreColumnRequest;
use App\Http\Requests\Column\UpdateColumnRequest;
use App\Http\Resources\ColumnResource;
use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function index(IndexColumnRequest $request, Project $project, Board $board)
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 50;

        $columns = $board->columns()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Colunas listadas com sucesso!',
            'data' => ColumnResource::collection($columns)->resource,
        ], 200);
    }

    public function show(Project $project, Board $board, Column $column)
    {
        $this->authorize('view', $column);

        return response()->json([
            'success' => true,
            'message' => 'Coluna encontrada com sucesso!',
            'data' => new ColumnResource($column),
        ], 200);
    }

    public function store(StoreColumnRequest $request, Project $project, Board $board)
    {
        $validated = $request->validated();

        $column = new Column;
        $column->board_id = $board->id;
        $column->user_id = $request->user()->id;
        $column->name = $validated['name'];
        $column->position = $validated['position'];
        $column->save();

        return response()->json([
            'success' => true,
            'message' => 'Coluna criada com sucesso!',
            'data' => new ColumnResource($column),
        ], 201);
    }

    public function update(UpdateColumnRequest $request, Project $project, Board $board, Column $column)
    {
        $validated = $request->validated();

        $column->name = $validated['name'];
        $column->position = $validated['position'];
        $column->save();

        return response()->json([
            'success' => true,
            'message' => 'Coluna atualizada com sucesso!',
            'data' => new ColumnResource($column),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Board $board, Column $column)
    {
        $this->authorize('delete', $column);

        $deletedColumn = $column->replicate();
        $deletedColumn->id = $column->id;
        $column->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coluna deletada com sucesso!',
            'data' => new ColumnResource($deletedColumn),
        ], 200);
    }
}
