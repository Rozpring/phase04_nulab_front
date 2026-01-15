<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BacklogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AI分析
    Route::get('analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::get('analysis/report', [AnalysisController::class, 'report'])->name('analysis.report');

    // Backlog連携
    Route::get('backlog/settings', [BacklogController::class, 'settings'])->name('backlog.settings');
    Route::post('backlog/settings', [BacklogController::class, 'saveSettings']);
    Route::post('backlog/test-connection', [BacklogController::class, 'testConnection'])->name('backlog.test-connection');
    Route::get('backlog/projects', [BacklogController::class, 'projects'])->name('backlog.projects');
    Route::get('backlog/issues', [BacklogController::class, 'issues'])->name('backlog.issues');
    Route::post('backlog/import', [BacklogController::class, 'import'])->name('backlog.import');
    Route::delete('backlog/issues', [BacklogController::class, 'deleteIssues'])->name('backlog.delete-issues');

    // 計画生成
    Route::get('planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::post('planning/generate', [PlanningController::class, 'generate'])->name('planning.generate');
    Route::get('planning/timeline', [PlanningController::class, 'timeline'])->name('planning.timeline');
    Route::get('planning/calendar', [PlanningController::class, 'calendar'])->name('planning.calendar');

    // ガントチャート
    Route::get('planning/gantt', [PlanningController::class, 'gantt'])->name('planning.gantt');
    Route::post('/api/tasks/{task}/update-dates', [PlanningController::class, 'updateDates'])->name('tasks.updateDates');
});

require __DIR__.'/auth.php';

