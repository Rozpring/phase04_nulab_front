<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with progress and today's plans.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // 今日の計画を取得（円グラフとリストで共通使用）
        $todayPlans = StudyPlan::where('user_id', $userId)
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get();

        // 総計画時間
        $totalHours = $todayPlans->sum('duration_minutes') / 60;

        // 進捗状況（今日の計画のステータス別集計）
        $progress = [
            'total' => $todayPlans->count(),
            'not_started' => $todayPlans->where('status', 'planned')->count(),
            'in_progress' => $todayPlans->where('status', 'in_progress')->count(),
            'processed' => $todayPlans->where('status', 'skipped')->count(),
            'completed' => $todayPlans->where('status', 'completed')->count(),
        ];

        // 完了率を計算
        $progress['completion_rate'] = $progress['total'] > 0 
            ? round(($progress['completed'] / $progress['total']) * 100) 
            : 0;

        // ステータス別のタスクリスト（吹き出し表示用）
        $tasksByStatus = [
            'not_started' => $todayPlans->where('status', 'planned')->values(),
            'in_progress' => $todayPlans->where('status', 'in_progress')->values(),
            'processed' => $todayPlans->where('status', 'skipped')->values(),
            'completed' => $todayPlans->where('status', 'completed')->values(),
        ];

        // 期限間近の課題（7日以内）
        $upcomingDeadlines = ImportedIssue::where('user_id', $userId)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // 最近の活動（3日以内の完了・スキップ）
        $recentActivity = StudyPlan::where('user_id', $userId)
            ->where('updated_at', '>=', now()->subDays(3))
            ->whereIn('status', ['completed', 'skipped'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todayPlans',
            'totalHours',
            'progress',
            'tasksByStatus',
            'upcomingDeadlines',
            'recentActivity'
        ));
    }
}
