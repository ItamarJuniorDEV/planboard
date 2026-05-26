<?php

namespace App\Http\Controllers;

use App\Http\Requests\Board\IndexBoardRequest;
use App\Http\Requests\Board\StoreBoardRequest;
use App\Http\Requests\Board\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(IndexBoardRequest $request, Project $project)
    {
        $validate = $request->validated();

        $perPage = $validate['per_page'] ?? 20;

        $query = $project->boards();

        if (isset($validate['status'])) {
            $query->where('status', $validate['status']);
        }

        if (isset($validate['search'])) {
            $query->where('name', 'LIKE', '%'.$validate['search'].'%');
        }

        $boards = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Quadros listados com sucesso!',
            'data' => BoardResource::collection($boards)->resource,
        ], 200);
    }

    public function show(Project $project, Board $board)
    {
        $this->authorize('view', $board);

        return response()->json([
            'success' => true,
            'message' => 'Quadro encontrado com sucesso!',
            'data' => new BoardResource($board),
        ], 200);
    }

    public function store(StoreBoardRequest $request, Project $project)
    {
        $validate = $request->validated();

        $board = new Board;
        $board->project_id = $project->id;
        $board->user_id = $request->user()->id;
        $board->name = $validate['name'];
        $board->status = $validate['status'];
        $board->save();

        return response()->json([
            'success' => true,
            'message' => 'Quadro criado com sucesso!',
            'data' => new BoardResource($board),
        ], 201);
    }

    public function update(UpdateBoardRequest $request, Project $project, Board $board)
    {
        $validate = $request->validated();

        $board->name = $validate['name'];
        $board->status = $validate['status'];
        $board->save();

        return response()->json([
            'success' => true,
            'message' => 'Quadro atualizado com sucesso!',
            'data' => new BoardResource($board),
        ], 200);
    }

    public function destroy(Request $request, Project $project, Board $board)
    {
        $this->authorize('delete', $board);

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quadro excluído com sucesso!',
            'data' => new BoardResource($board),
        ], 200);
    }
}
