<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\PlanningController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// セッション認証を使用するAPIルート
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/planning/generate', [PlanningController::class, 'apiGenerate']);
    Route::get('/planning/unscheduled', [PlanningController::class, 'apiUnscheduled']);
    Route::patch('/planning/tasks/{studyPlan}/status', [PlanningController::class, 'updateStatus']);
    Route::post('/analysis/advice', [AnalysisController::class, 'apiAdvice']);
});

