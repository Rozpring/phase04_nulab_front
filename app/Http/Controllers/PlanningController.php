<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\Task;//追加（岡部条）
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class PlanningController extends Controller
{
    /**
     * 計画ダッシュボード
     */
    // ▼ 変更: Request $request を引数に追加
    public function index(Request $request): View
    {
        $userId = Auth::id();
        
        // インポート済み課題
        $importedIssues = ImportedIssue::where('user_id', $userId)
            ->whereNotIn('status', ['完了', '処理済み'])
            ->orderBy('due_date')
            ->get();

        // 今日の計画
        $todayPlans = StudyPlan::where('user_id', $userId)
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get();

        // 今週の計画
        $weekPlans = StudyPlan::where('user_id', $userId)
            ->whereBetween('scheduled_date', [today(), today()->addDays(6)])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get()
            ->groupBy(function ($plan) {
                return $plan->scheduled_date->format('Y-m-d');
            });

        // 統計
        $stats = [
            'pending_issues' => $importedIssues->count(),
            'today_plans' => $todayPlans->count(),
            'today_hours' => $todayPlans->sum('duration_minutes') / 60,
            'week_plans' => StudyPlan::where('user_id', $userId)
                ->whereBetween('scheduled_date', [today(), today()->addDays(6)])
                ->count(),
        ];

        // 追加: ガントチャート用データの取得ロジック           岡部条（追加）
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // その月に表示すべきタスクをDBから取得
        $ganttTasks = Task::where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                    $q2->where('start_date', '<', $startOfMonth)
                    ->where('end_date', '>', $endOfMonth);
                });
            })
            ->orderBy('start_date')
            ->get();
        // 追加ここまで 

        // viewに year, month, ganttTasks を追加して渡す
        return view('planning.index', compact('importedIssues', 'todayPlans', 'weekPlans', 'stats', 'year', 'month', 'ganttTasks'));
    }

    /**
     * AI計画生成（モック）
     */
    public function generate(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        
        // インポート済み課題を取得
        $issues = ImportedIssue::where('user_id', $userId)
            ->whereNotIn('status', ['完了', '処理済み'])
            ->orderByRaw("CASE 
                WHEN priority = '高' THEN 1 
                WHEN priority = '中' THEN 2 
                ELSE 3 
            END")
            ->orderBy('due_date')
            ->get();

        if ($issues->isEmpty()) {
            return redirect()->route('planning.index')
                ->with('warning', '計画を生成するには、まずBacklogから課題をインポートしてください');
        }

        // 今日以降の既存計画をクリア（重複を防ぐ）
        StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', today())
            ->where('status', 'planned')
            ->delete();

        // AI計画生成ロジック（モック）
        $this->generateMockPlans($userId, $issues);

        return redirect()->route('planning.index')
            ->with('success', 'AI計画を生成しました！今日からの学習スケジュールを確認してください');
    }

    /**
     * AI計画生成 API
     */
    public function apiGenerate(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        // インポート済み課題を取得
        $issues = ImportedIssue::where('user_id', $userId)
            ->whereNotIn('status', ['完了', '処理済み'])
            ->orderByRaw("CASE 
                WHEN priority = '高' THEN 1 
                WHEN priority = '中' THEN 2 
                ELSE 3 
            END")
            ->orderBy('due_date')
            ->get();

        if ($issues->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '計画を生成するには、まずBacklogから課題をインポートしてください',
                'plans' => [],
                'target_date' => today()->format('Y-m-d'),
            ], 400);
        }

        // 今日以降の既存計画をクリア（重複を防ぐ）
        StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', today())
            ->where('status', 'planned')
            ->delete();

        // AI計画生成ロジック（モック）
        $this->generateMockPlans($userId, $issues);

        // 生成された計画を取得（関連するImportedIssueも取得）
        $plans = StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', today())
            ->with('importedIssue')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        // 期待されるレスポンス形式に変換
        $formattedPlans = $plans->map(function ($plan) {
            // 数値優先度を日本語文字列に変換
            $priorityMap = [
                9 => '高',
                5 => '中',
                3 => '低',
            ];
            $priorityString = $priorityMap[$plan->priority] ?? '中';

            return [
                'id' => $plan->id,
                'issue_key' => $plan->importedIssue?->issue_key ?? null,
                'title' => $plan->title,
                'planned_minutes' => $plan->duration_minutes,
                'priority' => $priorityString,
                'ai_comment' => $plan->ai_reason,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => $plans->count() . '件の計画を生成しました',
            'plans' => $formattedPlans,
            'target_date' => today()->format('Y-m-d'),
        ]);
    }

    /**
     * モック計画生成
     */
    private function generateMockPlans(int $userId, $issues): void
    {
        $currentDate = today();
        $startHour = 9; // 9:00開始
        $endHour = 18; // 18:00終了
        $currentHour = $startHour;
        $lunchBreakAdded = false;

        foreach ($issues as $issue) {
            $duration = ($issue->estimated_hours ?? 2) * 60; // 分単位
            
            // 最大4時間のブロックに分割
            $blocks = ceil($duration / 240);
            $blockDuration = $duration / $blocks;

            for ($i = 0; $i < $blocks; $i++) {
                // 昼休みを挿入
                if ($currentHour >= 12 && !$lunchBreakAdded) {
                    StudyPlan::create([
                        'user_id' => $userId,
                        'title' => '昼休み',
                        'plan_type' => 'break',
                        'scheduled_date' => $currentDate,
                        'scheduled_time' => Carbon::createFromTime(12, 0),
                        'end_time' => Carbon::createFromTime(13, 0),
                        'duration_minutes' => 60,
                        'priority' => 10,
                        'ai_reason' => '午後の作業効率を維持するための休憩時間',
                    ]);
                    $currentHour = 13;
                    $lunchBreakAdded = true;
                }

                // 1日の終了時間を超えたら翌日へ
                if ($currentHour + ($blockDuration / 60) > $endHour) {
                    $currentDate = $currentDate->addDay();
                    $currentHour = $startHour;
                    $lunchBreakAdded = false;
                }

                $endMinutes = $currentHour * 60 + $blockDuration;
                $endTime = Carbon::createFromTime(floor($endMinutes / 60), $endMinutes % 60);

                // AIの推奨理由を生成
                $reason = $this->generateAiReason($issue, $i === 0);

                StudyPlan::create([
                    'user_id' => $userId,
                    'imported_issue_id' => $issue->id,
                    'title' => $issue->summary,
                    'description' => $issue->description,
                    'plan_type' => str_contains(strtolower($issue->issue_key), 'study') ? 'study' : 'work',
                    'scheduled_date' => $currentDate,
                    'scheduled_time' => Carbon::createFromTime($currentHour, 0),
                    'end_time' => $endTime,
                    'duration_minutes' => (int) $blockDuration,
                    'priority' => $issue->priority === '高' ? 9 : ($issue->priority === '中' ? 5 : 3),
                    'ai_reason' => $reason,
                ]);

                $currentHour += $blockDuration / 60;
            }

            // 休憩を挿入（2時間以上作業後）
            // ただし、昼休み（12:00〜13:00）の前後1時間は小休憩を挿入しない
            $nearLunchBreak = ($currentHour >= 11 && $currentHour <= 14);
            if ($currentHour - $startHour >= 2 && $currentHour < $endHour - 0.5 && !$nearLunchBreak) {
                StudyPlan::create([
                    'user_id' => $userId,
                    'title' => '小休憩',
                    'plan_type' => 'break',
                    'scheduled_date' => $currentDate,
                    'scheduled_time' => Carbon::createFromTime((int)$currentHour, 0),
                    'end_time' => Carbon::createFromTime((int)$currentHour, 15),
                    'duration_minutes' => 15,
                    'priority' => 10,
                    'ai_reason' => '集中力を維持するための短い休憩',
                ]);
                $currentHour += 0.25;
            }
        }
    }

    /**
     * AIの推奨理由を生成
     */
    private function generateAiReason($issue, bool $isFirst): string
    {
        $reasons = [];

        if ($issue->priority === '高') {
            $reasons[] = '優先度が高いため早めに着手することを推奨';
        }

        if ($issue->due_date && $issue->days_until_due !== null) {
            if ($issue->days_until_due <= 2) {
                $reasons[] = "期限まであと{$issue->days_until_due}日のため緊急対応";
            } elseif ($issue->days_until_due <= 5) {
                $reasons[] = '期限が近づいているため計画的に進める必要あり';
            }
        }

        if ($isFirst && now()->hour < 12) {
            $reasons[] = '午前中は集中力が高いため、難しいタスクを配置';
        }

        if (empty($reasons)) {
            $reasons[] = 'バランスの取れたスケジュールのため配置';
        }

        return implode('。', $reasons) . '。';
    }

    /**
     * タイムライン表示
     */
    public function timeline(Request $request): View
    {
        $userId = Auth::id();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : today();

        $plans = StudyPlan::where('user_id', $userId)
            ->whereDate('scheduled_date', $date)
            ->orderBy('scheduled_time')
            ->get();

        // 時間スロット（6:00〜23:00）
        $timeSlots = [];
        for ($hour = 6; $hour <= 23; $hour++) {
            $timeSlots[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'plans' => $plans->filter(function ($plan) use ($hour) {
                    if (!$plan->scheduled_time) return false;
                    return $plan->scheduled_time->hour === $hour;
                }),
            ];
        }

        return view('planning.timeline', compact('plans', 'timeSlots', 'date'));
    }

    /**
     * カレンダー表示
     */
    public function calendar(Request $request): View
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // 月の計画を取得
        $plans = StudyPlan::where('user_id', $userId)
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->orderBy('scheduled_time')
            ->get()
            ->groupBy(function ($plan) {
                return $plan->scheduled_date->format('Y-m-d');
            });

        //追加（岡部条）
        $ganttTasks = Task::orderBy('start_date')->get()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'status' => $task->status ?? 'gray',
            ];
        })->toArray();
        //追加ここまで

        // カレンダー用のデータを生成
        $calendar = [];
        $currentDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        while ($currentDate <= $endDate) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');
                $week[] = [
                    'date' => $currentDate->copy(),
                    'day' => $currentDate->day,
                    'isCurrentMonth' => $currentDate->month === (int)$month,
                    'isToday' => $currentDate->isToday(),
                    'plans' => $plans->get($dateKey, collect()),
                ];
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }

        return view('planning.calendar', compact('calendar', 'year', 'month', 'startOfMonth', 'ganttTasks'));
    }

    /**
     * ガントチャート表示
     */
    /**
     * ガントチャート表示
     */
    public function gantt(Request $request): View
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        //追加（岡部条）
        $ganttTasks = Task::orderBy('start_date')->get()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'status' => $task->status ?? 'gray',
            ];
        })->toArray();

        return view('planning.gantt', compact('ganttTasks', 'year', 'month', 'daysInMonth'));
    }

    //追加（岡部条）
    public function updateDates(Request $request, Task $task)
    {
        // 1. バリデーション
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        // 2. 更新
        $task->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
        ]);

        // 3. レスポンス
        return response()->json([
            'success' => true,
            'message' => 'スケジュールを更新しました',
            'task' => $task
        ]);
    }

    /**
     * タスクのステータス更新 API
     */
    public function updateStatus(Request $request, StudyPlan $studyPlan): JsonResponse
    {
        // ユーザー認可チェック
        if ($studyPlan->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => '権限がありません',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:planned,in_progress,completed,skipped'],
        ]);

        $studyPlan->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ステータスを更新しました',
            'plan' => $studyPlan,
        ]);
    }
}