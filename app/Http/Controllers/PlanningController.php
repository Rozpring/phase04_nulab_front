<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanningController extends Controller
{
    /**
     * 1. 計画ダッシュボード (カンバン & KPI)
     */
    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1; // ログインなしなら仮ID 1

        // A. データベースから今日のタスクを取得 (daily_plans)
        $tasks = DB::table('daily_plans')
            ->select('id', 'lane_status', 'target_date', 'raw_issue_id')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'title' => 'タスク ID:' . $plan->id,
                    'lane_status' => $plan->lane_status,
                    'plan_type' => 'work',
                    'scheduled_time' => '10:00',
                    'end_time' => '11:00',
                    'duration_minutes' => 60,
                ];
            });

        // B. インポート済み課題 (raw_backlog_issues)
        // ★修正: カラム名エラー回避のため一時的に無効化
        $importedIssues = DB::table('raw_backlog_issues')
            // ->where('status', '!=', '完了') 
            // ->orderBy('due_date')
            ->limit(5)
            ->get();

        // C. 統計データ
        $stats = [
            'pending_issues' => $importedIssues->count(),
            'today_plans' => $tasks->count(),
            'today_hours' => $tasks->sum('duration_minutes') / 60,
            'week_plans' => 0 
        ];

        // D. ガントチャート用データ
        $ganttTasks = $tasks->map(function($task) {
            return [
                'id' => $task['id'],
                'title' => $task['title'],
                'start_date' => now()->format('Y-m-d'), // 仮
                'end_date' => now()->addDays(1)->format('Y-m-d'), // 仮
                'status' => 'blue',
            ];
        });

        // ビューに渡す
        return view('planning.index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'importedIssues' => $importedIssues,
            'weekPlans' => collect(),
            'ganttTasks' => $ganttTasks,
            'year' => $request->input('year', now()->year),
            'month' => $request->input('month', now()->month),
        ]);
    }

    /**
     * 2. AI計画生成処理 (モック)
     */
    public function generate(Request $request)
    {
        $userId = Auth::id() ?? 1;

        // 1. Backlogの課題を取得
        $issues = DB::table('raw_backlog_issues')
            // ->where('status', '!=', '完了')
            ->get();

        if ($issues->isEmpty()) {
            return redirect()->back()->with('warning', '課題がありません');
        }

        // 2. 既存の「予定」をクリア
        DB::table('daily_plans')
            ->where('user_id', $userId)
            ->where('lane_status', 'planned')
            ->delete();

        // 3. AIロジック
        foreach ($issues as $issue) {
            DB::table('daily_plans')->insert([
                'user_id' => $userId,
                'raw_issue_id' => $issue->id,
                'target_date' => now(),
                'lane_status' => 'planned',
                'planned_minutes' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('planning.index')
            ->with('success', 'AI計画を生成しました！');
    }

    /**
     * 3. タイムライン表示
     */
    public function timeline(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : today();
        $plans = DB::table('daily_plans')->whereDate('target_date', $date)->get();
        return view('planning.timeline', compact('plans', 'date'));
    }

    /**
     * 4. カレンダー表示
     */
    public function calendar(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        return view('planning.calendar', compact('year', 'month'));
    }

    /**
     * 5. ガントチャート表示
     */
    public function gantt(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $ganttTasks = DB::table('daily_plans')->get();
        return view('planning.gantt', compact('ganttTasks', 'year', 'month'));
    }

    /**
     * 6. API: 日付更新
     */
    public function updateDates(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        DB::table('daily_plans')
            ->where('id', $id)
            ->update([
                'target_date' => $request->start_date,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * 7. ドラッグ＆ドロップ用ステータス更新
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:planned,in_progress,completed'
        ]);

        DB::table('daily_plans')
            ->where('id', $id)
            ->update([
                'lane_status' => $request->status,
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Status updated successfully']);
    }
}