<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    // projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{project}/stats', [ProjectController::class, 'stats']);

    // boards
    Route::scopeBindings()->group(function () {
        Route::get('/projects/{project}/boards', [BoardController::class, 'index']);
        Route::get('/projects/{project}/boards/{board}', [BoardController::class, 'show']);
        Route::post('/projects/{project}/boards', [BoardController::class, 'store']);
        Route::put('/projects/{project}/boards/{board}', [BoardController::class, 'update']);
        Route::delete('/projects/{project}/boards/{board}', [BoardController::class, 'destroy']);

        // columns
        Route::get('/projects/{project}/boards/{board}/columns', [ColumnController::class, 'index']);
        Route::get('/projects/{project}/boards/{board}/columns/{column}', [ColumnController::class, 'show']);
        Route::post('/projects/{project}/boards/{board}/columns', [ColumnController::class, 'store']);
        Route::put('/projects/{project}/boards/{board}/columns/{column}', [ColumnController::class, 'update']);
        Route::delete('/projects/{project}/boards/{board}/columns/{column}', [ColumnController::class, 'destroy']);

    });

    // tasks scoped to project
    Route::scopeBindings()->group(function () {
        Route::patch('/projects/{project}/tasks/bulk-move', [TaskController::class, 'bulkMove']);
        Route::post('/projects/{project}/tasks/bulk-delete', [TaskController::class, 'bulkDelete']);
        Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
        Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy']);
        Route::patch('/projects/{project}/tasks/{task}/move', [TaskController::class, 'move']);

        // subtasks
        Route::post('/projects/{project}/tasks/{task}/subtasks/bulk-complete', [SubtaskController::class, 'bulkComplete']);
        Route::post('/projects/{project}/tasks/{task}/subtasks/bulk-delete', [SubtaskController::class, 'bulkDelete']);
        Route::get('/projects/{project}/tasks/{task}/subtasks', [SubtaskController::class, 'index']);
        Route::post('/projects/{project}/tasks/{task}/subtasks', [SubtaskController::class, 'store']);
        Route::get('/projects/{project}/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'show']);
        Route::put('/projects/{project}/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update']);
        Route::delete('/projects/{project}/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy']);

        // comments
        Route::post('/projects/{project}/tasks/{task}/comments/bulk-delete', [CommentController::class, 'bulkDelete']);
        Route::get('/projects/{project}/tasks/{task}/comments', [CommentController::class, 'index']);
        Route::post('/projects/{project}/tasks/{task}/comments', [CommentController::class, 'store']);
        Route::get('/projects/{project}/tasks/{task}/comments/{comment}', [CommentController::class, 'show']);
        Route::put('/projects/{project}/tasks/{task}/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/projects/{project}/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy']);
    });

    // milestones
    Route::scopeBindings()->group(function () {
        Route::get('/projects/{project}/milestones', [MilestoneController::class, 'index']);
        Route::get('/projects/{project}/milestones/{milestone}', [MilestoneController::class, 'show']);
        Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store']);
        Route::put('/projects/{project}/milestones/{milestone}', [MilestoneController::class, 'update']);
        Route::delete('/projects/{project}/milestones/{milestone}', [MilestoneController::class, 'destroy']);
    });

    // labels
    Route::scopeBindings()->group(function () {
        Route::get('/projects/{project}/labels', [LabelController::class, 'index']);
        Route::get('/projects/{project}/labels/{label}', [LabelController::class, 'show']);
        Route::post('/projects/{project}/labels', [LabelController::class, 'store']);
        Route::put('/projects/{project}/labels/{label}', [LabelController::class, 'update']);
        Route::delete('/projects/{project}/labels/{label}', [LabelController::class, 'destroy']);
    });

    // users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store'])->middleware('role:admin');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('role:admin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('role:admin');
});
