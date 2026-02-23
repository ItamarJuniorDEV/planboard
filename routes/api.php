<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// projects
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::put('/projects/{id}', [ProjectController::class, 'update']);
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
Route::get('/projects/{projectId}/stats', [ProjectController::class, 'stats']);

// boards
Route::get('/projects/{projectId}/boards', [BoardController::class, 'index']);
Route::get('/projects/{projectId}/boards/{id}', [BoardController::class, 'show']);
Route::post('/projects/{projectId}/boards', [BoardController::class, 'store']);
Route::put('/projects/{projectId}/boards/{id}', [BoardController::class, 'update']);
Route::delete('/projects/{projectId}/boards/{id}', [BoardController::class, 'destroy']);

// columns
Route::get('/projects/{projectId}/boards/{boardId}/columns', [ColumnController::class, 'index']);
Route::get('/projects/{projectId}/boards/{boardId}/columns/{id}', [ColumnController::class, 'show']);
Route::post('/projects/{projectId}/boards/{boardId}/columns', [ColumnController::class, 'store']);
Route::put('/projects/{projectId}/boards/{boardId}/columns/{id}', [ColumnController::class, 'update']);
Route::delete('/projects/{projectId}/boards/{boardId}/columns/{id}', [ColumnController::class, 'destroy']);

// tasks
Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
Route::get('/projects/{projectId}/tasks/{id}', [TaskController::class, 'show']);
Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);
Route::put('/projects/{projectId}/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/projects/{projectId}/tasks/{id}', [TaskController::class, 'destroy']);
Route::patch('/projects/{projectId}/boards/{boardId}/columns/{columnId}/tasks/{taskId}/move', [TaskController::class, 'moveToColumn']);
Route::patch('/projects/{projectId}/tasks/bulk-move', [TaskController::class, 'bulkMove']);
Route::post('/projects/{projectId}/tasks/bulk-delete', [TaskController::class, 'bulkDelete']);

// subtasks
Route::get('/projects/{projectId}/tasks/{taskId}/subtasks', [SubtaskController::class, 'index']);
Route::get('/projects/{projectId}/tasks/{taskId}/subtasks/{id}', [SubtaskController::class, 'show']);
Route::post('/projects/{projectId}/tasks/{taskId}/subtasks', [SubtaskController::class, 'store']);
Route::put('/projects/{projectId}/tasks/{taskId}/subtasks/{id}', [SubtaskController::class, 'update']);
Route::delete('/projects/{projectId}/tasks/{taskId}/subtasks/{id}', [SubtaskController::class, 'destroy']);
Route::post('/projects/{projectId}/tasks/{taskId}/subtasks/bulk-complete', [SubtaskController::class, 'bulkComplete']);
Route::post('/projects/{projectId}/tasks/{taskId}/subtasks/bulk-delete', [SubtaskController::class, 'bulkDelete']);

// comments
Route::get('/projects/{projectId}/tasks/{taskId}/comments', [CommentController::class, 'index']);
Route::get('/projects/{projectId}/tasks/{taskId}/comments/{id}', [CommentController::class, 'show']);
Route::post('/projects/{projectId}/tasks/{taskId}/comments', [CommentController::class, 'store']);
Route::put('/projects/{projectId}/tasks/{taskId}/comments/{id}', [CommentController::class, 'update']);
Route::delete('/projects/{projectId}/tasks/{taskId}/comments/{id}', [CommentController::class, 'destroy']);
Route::post('/projects/{projectId}/tasks/{taskId}/comments/bulk-delete', [CommentController::class, 'bulkDelete']);

// marco
Route::get('/projects/{projectId}/milestones', [MilestoneController::class, 'index']);
Route::get('/projects/{projectId}/milestones/{id}', [MilestoneController::class, 'show']);
Route::post('/projects/{projectId}/milestones', [MilestoneController::class, 'store']);
Route::put('/projects/{projectId}/milestones/{id}', [MilestoneController::class, 'update']);
Route::delete('/projects/{projectId}/milestones/{id}', [MilestoneController::class, 'destroy']);

// categorias
Route::get('/projects/{projectId}/labels', [LabelController::class, 'index']);
Route::get('/projects/{projectId}/labels/{id}', [LabelController::class, 'show']);
Route::post('/projects/{projectId}/labels', [LabelController::class, 'store']);
Route::put('/projects/{projectId}/labels/{id}', [LabelController::class, 'update']);
Route::delete('/projects/{projectId}/labels/{id}', [LabelController::class, 'destroy']);

// usuários
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
