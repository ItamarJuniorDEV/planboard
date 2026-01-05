<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// projects
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::put('/projects/{id}', [ProjectController::class, 'update']);
Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

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
