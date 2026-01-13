<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\Task;
use App\Services\PlanGenerationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function __construct(
        private readonly PlanGenerationService $planService
    ) {}

    /**
     * 計画ダッシュボード
     */
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

        // ガントチャート用データの取得
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

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

        return view('planning.index', compact('importedIssues', 'todayPlans', 'weekPlans', 'stats', 'year', 'month', 'ganttTasks'));
    }

    /**
     * AI計画生成（Web）
     */
    public function generate(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        
        $issues = $this->planService->getPendingIssues($userId);

        if ($issues->isEmpty()) {
            return redirect()->route('planning.index')
                ->with('warning', '計画を生成するには、まずBacklogから課題をインポートしてください');
        }

        $this->planService->clearPendingPlans($userId);
        $this->planService->generatePlans($userId, $issues);

        return redirect()->route('planning.index')
            ->with('success', 'AI計画を生成しました！今日からの学習スケジュールを確認してください');
    }

    /**
     * AI計画生成（API）
     */
    public function apiGenerate(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        $issues = $this->planService->getPendingIssues($userId);

        if ($issues->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '計画を生成するには、まずBacklogから課題をインポートしてください',
                'plans' => [],
                'target_date' => today()->format('Y-m-d'),
            ], 400);
        }

        $this->planService->clearPendingPlans($userId);
        $this->planService->generatePlans($userId, $issues);

        $formattedPlans = $this->planService->getFormattedPlans($userId);

        return response()->json([
            'success' => true,
            'message' => count($formattedPlans) . '件の計画を生成しました',
            'plans' => $formattedPlans,
            'target_date' => today()->format('Y-m-d'),
        ]);
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

        // ガントチャート用タスク
        $ganttTasks = Task::orderBy('start_date')->get()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'status' => $task->status ?? 'gray',
            ];
        })->toArray();

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
    public function gantt(Request $request): View
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

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

    /**
     * タスクの日付更新
     */
    public function updateDates(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $task->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'スケジュールを更新しました',
            'task' => $task
        ]);
    }

    /**
     * タスクのステータス更新（API）
     */
    public function updateStatus(Request $request, StudyPlan $studyPlan): JsonResponse
    {
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