<?php

use App\Http\Controllers\ActorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('actors.form');
});

Route::get('/actors/form', [ActorController::class, 'showForm'])->name('actors.form');
Route::post('/actors', [ActorController::class, 'store'])->name('actors.store');
Route::get('/actors/submissions', [ActorController::class, 'submissions'])->name('actors.submissions');

// API Routes
Route::get('/api/actors/prompt-validation', [ActorController::class, 'promptValidation']);

// Health Check Routes
Route::get('/health', [App\Http\Controllers\HealthController::class, 'index']);
Route::get('/health/database', [App\Http\Controllers\HealthController::class, 'database']);
Route::get('/health/cache', [App\Http\Controllers\HealthController::class, 'cache']);
Route::get('/health/openai', [App\Http\Controllers\HealthController::class, 'openai']);
