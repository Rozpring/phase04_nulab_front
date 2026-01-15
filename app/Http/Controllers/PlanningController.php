<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\Task;
use App\Services\BackendApiService;
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
        private readonly PlanGenerationService $planService,
        private readonly BackendApiService $backendApi
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
        $todayPlans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get();

        // 今週の計画
        $weekPlans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
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
     * バックエンドAPIを優先し、失敗時はローカルサービスにフォールバック
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

        // バックエンドAPIを試行（課題データを整形して送信）
        $issueData = $issues->map(fn($issue) => [
            'title' => $issue->summary,
            'description' => $issue->description,
            'priority' => $issue->priority,
            'dueDate' => $issue->due_date?->format('Y-m-d'),
            'estimatedHours' => $issue->estimated_hours,
            'issue_key' => $issue->issue_key,
        ])->toArray();

        $backendResponse = $this->backendApi->generatePlanning($issueData);
        
        if ($backendResponse && isset($backendResponse['success']) && $backendResponse['success']) {
            // バックエンドAPIから計画を取得成功
            // バックエンドの計画をフロントエンドのStudyPlanに同期
            $targetDate = $backendResponse['target_date'] ?? today()->format('Y-m-d');
            $this->syncBackendPlansToLocal($userId, $backendResponse['plans'] ?? [], $targetDate);
            
            return response()->json([
                'success' => true,
                'message' => $backendResponse['message'] ?? '計画を生成しました',
                'plans' => $this->planService->getFormattedPlans($userId),
                'target_date' => $backendResponse['target_date'] ?? today()->format('Y-m-d'),
                'source' => 'backend_api',
            ]);
        }

        // フォールバック: ローカルサービスで生成
        if ($this->backendApi->isFallbackEnabled()) {
            $this->planService->clearPendingPlans($userId);
            $this->planService->generatePlans($userId, $issues);

            $formattedPlans = $this->planService->getFormattedPlans($userId);

            return response()->json([
                'success' => true,
                'message' => count($formattedPlans) . '件の計画を生成しました（ローカル）',
                'plans' => $formattedPlans,
                'target_date' => today()->format('Y-m-d'),
                'source' => 'local_fallback',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'バックエンドAPIへの接続に失敗しました',
            'plans' => [],
            'target_date' => today()->format('Y-m-d'),
        ], 503);
    }

    /**
     * バックエンドAPIからの計画をローカルDBに同期
     * @param int $userId ユーザーID
     * @param array $plans 計画データの配列
     * @param string $targetDate 計画の対象日（Y-m-d形式）
     */
    private function syncBackendPlansToLocal(int $userId, array $plans, string $targetDate): void
    {
        // 既存の予定計画をクリア
        $this->planService->clearPendingPlans($userId);
        
        // 計画を順番にスケジュール（9:00開始、重複なし）
        $currentTime = Carbon::createFromTime(9, 0);
        
        foreach ($plans as $plan) {
            $durationMinutes = $plan['planned_minutes'] ?? 60;
            $endTime = $currentTime->copy()->addMinutes($durationMinutes);
            
            StudyPlan::create([
                'user_id' => $userId,
                'imported_issue_id' => null, // バックエンドのraw_issue_idとマッピングが必要な場合は別途実装
                'title' => $plan['title'] ?? '',
                'plan_type' => 'work',
                'scheduled_date' => $targetDate,
                'scheduled_time' => $currentTime->copy(),
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'priority' => $this->mapPriorityToNumber($plan['priority'] ?? '中'),
                'ai_reason' => $plan['ai_comment'] ?? null,
                'status' => 'planned',
            ]);
            
            // 次の計画の開始時刻を設定
            $currentTime = $endTime;
        }
    }

    /**
     * 優先度文字列を数値に変換
     */
    private function mapPriorityToNumber(string $priority): int
    {
        return match($priority) {
            '高' => 9,
            '中' => 5,
            '低' => 3,
            default => 5,
        };
    }


    /**
     * 未消化課題リスト取得（API）
     * バックエンドAPIを優先し、失敗時はローカルデータにフォールバック
     */
    public function apiUnscheduled(Request $request): JsonResponse
    {
        $userId = Auth::id();
        
        // バックエンドAPIを試行
        $backendResponse = $this->backendApi->getUnscheduledIssues();
        
        if ($backendResponse !== null) {
            // バックエンドAPIから取得成功
            return response()->json([
                'success' => true,
                'data' => $backendResponse,
                'source' => 'backend_api',
            ]);
        }
        
        // フォールバック: ローカルのImportedIssueから取得
        if ($this->backendApi->isFallbackEnabled()) {
            $scheduledIssueIds = StudyPlan::where('user_id', $userId)
                ->where('scheduled_date', '>=', today())
                ->whereNotNull('imported_issue_id')
                ->pluck('imported_issue_id')
                ->toArray();
            
            $unscheduledIssues = ImportedIssue::where('user_id', $userId)
                ->whereNotIn('status', ['完了', '処理済み'])
                ->whereNotIn('id', $scheduledIssueIds)
                ->orderBy('due_date')
                ->get()
                ->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'issue_key' => $issue->issue_key,
                        'summary' => $issue->summary,
                        'data' => [
                            'priority' => ['name' => $issue->priority],
                            'dueDate' => $issue->due_date?->format('Y-m-d'),
                            'estimatedHours' => $issue->estimated_hours,
                        ],
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $unscheduledIssues,
                'source' => 'local_fallback',
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'バックエンドAPIへの接続に失敗しました',
            'data' => [],
        ], 503);
    }

    /**
     * 今日のタスクボード取得（API）
     * ローカルのStudyPlanテーブルからデータを取得
     * （計画生成時にsyncBackendPlansToLocalでローカルDBに同期されている）
     */
    public function apiDaily(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $date = $request->input('date', today()->format('Y-m-d'));
        
        // ローカルのStudyPlanから取得
        $plans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
            ->whereDate('scheduled_date', $date)
            ->orderBy('scheduled_time')
            ->get();
        
        $lanes = [
            'planned' => [],
            'in_progress' => [],
            'completed' => [],
            'skipped' => [],
        ];
        
        foreach ($plans as $plan) {
            $laneStatus = $plan->status;
            if (!isset($lanes[$laneStatus])) {
                $laneStatus = 'planned';
            }
            
            $lanes[$laneStatus][] = [
                'id' => $plan->id,
                'issue_key' => $plan->importedIssue?->issue_key,
                'summary' => $plan->title,
                'lane_status' => $plan->status,
                'target_date' => $plan->scheduled_date->format('Y-m-d'),
                'end_date' => $plan->scheduled_date->format('Y-m-d'),
                'duration_minutes' => $plan->duration_minutes,
                'ai_comment' => $plan->ai_reason,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'lanes' => $lanes,
            ],
            'source' => 'local_db',
        ]);
    }

    /**
     * タイムライン表示


     */
    public function timeline(Request $request): View
    {
        $userId = Auth::id();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : today();

        $plans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
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
        $plans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
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
     * フロントエンドのStudyPlanを更新し、バックエンドAPIにも同期
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

        // ローカルのStudyPlanを更新
        $studyPlan->update([
            'status' => $validated['status'],
        ]);

        // バックエンドAPIに同期（失敗しても無視）
        $backendResponse = $this->backendApi->updateTaskStatus($studyPlan->id, $validated['status']);
        
        $response = [
            'success' => true,
            'message' => 'ステータスを更新しました',
            'plan' => $studyPlan,
            'new_lane_status' => $validated['status'],
        ];

        // バックエンドからresult_statusが返された場合は追加
        if ($backendResponse && isset($backendResponse['new_result_status'])) {
            $response['new_result_status'] = $backendResponse['new_result_status'];
        }

        return response()->json($response);
    }
}