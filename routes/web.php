<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? '/dashboard' : '/login');
});

Route::get('/version', VersionController::class);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', ProjectController::class)->except(['edit'])->parameters(['projects' => 'id']);
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');

    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    Route::get('/projects/{project}/tasks/{task}/edit', [TaskController::class, 'edit']);
    Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/projects/{project}/tasks/{task}/toggle', [TaskController::class, 'toggle']);

    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::delete('/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy']);
});
