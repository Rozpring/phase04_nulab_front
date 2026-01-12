<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    /**
     * Display the AI analysis dashboard.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // ImportedIssueとStudyPlanから統計を取得
        $issues = ImportedIssue::where('user_id', $userId)->get();
        $plans = StudyPlan::where('user_id', $userId)->get();
        
        $stats = $this->calculateStats($issues, $plans);
        $patterns = $this->detectPatterns($issues, $plans);
        $advice = $this->generateAdvice($patterns, $stats);
        $weeklyData = $this->getWeeklyData($userId);

        return view('analysis.index', compact('stats', 'patterns', 'advice', 'weeklyData'));
    }

    /**
     * Display the weekly/monthly report page.
     */
    public function report(): View
    {
        return view('analysis.report');
    }

    /**
     * AI分析アドバイスAPI
     */
    public function apiAdvice(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $targetDate = $request->input('date', today()->format('Y-m-d'));
        $refresh = $request->boolean('refresh', false);
        
        $cacheKey = "analysis_advice_{$userId}_{$targetDate}";
        
        // キャッシュ削除リクエストの場合
        if ($refresh) {
            Cache::forget($cacheKey);
        }
        
        // キャッシュをチェック
        $cached = Cache::has($cacheKey);
        
        $advice = Cache::remember($cacheKey, now()->addHours(1), function () use ($userId, $targetDate) {
            return $this->generateApiAdvice($userId, $targetDate);
        });
        
        return response()->json([
            'success' => true,
            'cached' => $cached && !request()->boolean('refresh'),
            'data' => [
                'target_date' => $targetDate,
                'advice' => $advice,
            ],
        ]);
    }

    /**
     * API用アドバイス生成
     */
    private function generateApiAdvice(int $userId, string $targetDate): array
    {
        $startDate = now()->subDays(7);
        $plans = StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', $startDate)
            ->get();
        
        $totalPlans = $plans->count();
        $completedPlans = $plans->where('status', 'completed')->count();
        $skippedPlans = $plans->where('status', 'skipped')->count();
        $plannedPlans = $plans->where('status', 'planned')->count();
        
        $advice = [];
        
        // 未着手タスクがある場合
        if ($plannedPlans > 0) {
            $advice[] = [
                'title' => '未着手タスクへの着手',
                'description' => "登録された{$plannedPlans}件のタスクが未着手状態です。まずは1件だけでも着手し、タスクの状態を更新する習慣をつけましょう。",
                'tag' => '緊急',
                'type' => 'warning',
            ];
        }
        
        // 完了タスクがない場合
        if ($completedPlans === 0 && $totalPlans > 0) {
            $advice[] = [
                'title' => 'タスクの細分化と完了体験',
                'description' => '完了タスクがないため、タスクが大きすぎる可能性があります。小さく分割し、短時間で完了できるタスクから取り組み、達成感を増やしましょう。',
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // スキップ率が高い場合
        if ($totalPlans > 0 && ($skippedPlans / $totalPlans) > 0.2) {
            $skipRate = round(($skippedPlans / $totalPlans) * 100);
            $advice[] = [
                'title' => '計画の見直し',
                'description' => "スキップ率が{$skipRate}%と高めです。見積もり時間を短くするか、タスクを分割して取り組みやすくしましょう。",
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // データが少ない場合
        if ($totalPlans === 0) {
            $advice[] = [
                'title' => '日々の作業記録の徹底',
                'description' => '日別の作業記録がありません。タスクに着手したら、必ず実績時間や進捗状況を記録する習慣をつけましょう。',
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 良いパフォーマンスの場合
        if ($totalPlans > 0 && $completedPlans > 0) {
            $completionRate = round(($completedPlans / $totalPlans) * 100);
            if ($completionRate >= 70) {
                $advice[] = [
                    'title' => '素晴らしい進捗です',
                    'description' => "完了率{$completionRate}%は優秀です。この調子で継続しましょう！",
                    'tag' => '参考',
                    'type' => 'info',
                ];
            }
        }
        
        // 最低3件のアドバイスを返す
        $defaultAdvice = [
            [
                'title' => '継続的な記録の重要性',
                'description' => '毎日の作業を記録することで、AIがより正確なアドバイスを提供できるようになります。',
                'tag' => '参考',
                'type' => 'info',
            ],
        ];
        
        while (count($advice) < 3) {
            $advice[] = array_shift($defaultAdvice) ?? [
                'title' => '定期的な振り返り',
                'description' => '週に一度、完了したタスクと未完了のタスクを振り返り、次週の計画に活かしましょう。',
                'tag' => '参考',
                'type' => 'info',
            ];
        }
        
        return array_slice($advice, 0, 3);
    }


    /**
     * 統計情報を計算
     */
    private function calculateStats($issues, $plans): array
    {
        $totalIssues = $issues->count();
        $totalPlans = $plans->count();
        $completedPlans = $plans->where('status', 'completed')->count();
        $inProgress = $plans->where('status', 'in_progress')->count();
        $skippedPlans = $plans->where('status', 'skipped')->count();

        // カテゴリ別完了率（プランタイプ別）
        $categoryStats = [];
        foreach (['study', 'work', 'review'] as $type) {
            $typePlans = $plans->where('plan_type', $type);
            $typeTotal = $typePlans->count();
            $typeCompleted = $typePlans->where('status', 'completed')->count();
            $categoryStats[$type] = [
                'total' => $typeTotal,
                'completed' => $typeCompleted,
                'rate' => $typeTotal > 0 ? round(($typeCompleted / $typeTotal) * 100) : 0,
            ];
        }

        return [
            'total' => $totalIssues,
            'completed' => $completedPlans,
            'failed' => $skippedPlans,
            'in_progress' => $inProgress,
            'pending' => $totalIssues - $completedPlans,
            'completion_rate' => $totalPlans > 0 ? round(($completedPlans / $totalPlans) * 100) : 0,
            'failure_rate' => $totalPlans > 0 ? round(($skippedPlans / $totalPlans) * 100) : 0,
            'estimation_accuracy' => 85, // モック値
            'by_category' => $categoryStats,
        ];
    }

    /**
     * パターンを検出
     */
    private function detectPatterns($issues, $plans): array
    {
        $patterns = [];

        // 期限切れの課題が多い場合
        $overdue = $issues->filter(function ($issue) {
            return $issue->is_overdue;
        })->count();
        
        if ($overdue > 2) {
            $patterns[] = [
                'type' => 'deadline_miss',
                'severity' => 'critical',
                'icon' => 'clock',
                'title' => '締め切り超過',
                'message' => "{$overdue}件の課題が締め切りを過ぎています。優先順位を見直しましょう。",
                'frequency' => $overdue,
            ];
        }

        // スキップされた計画が多い場合
        $totalPlans = $plans->count();
        $skipped = $plans->where('status', 'skipped')->count();
        if ($totalPlans > 0 && ($skipped / $totalPlans) > 0.3) {
            $patterns[] = [
                'type' => 'high_skip_rate',
                'severity' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'スキップ率が高い',
                'message' => "計画の {$skipped}/{$totalPlans} がスキップされています。計画の粒度を見直しましょう。",
                'frequency' => $skipped,
            ];
        }

        // データが少ない場合
        if ($issues->count() < 5) {
            $patterns[] = [
                'type' => 'sample_pattern',
                'severity' => 'info',
                'icon' => 'light-bulb',
                'title' => 'データ収集中',
                'message' => 'より正確な分析のために、Backlogから課題をインポートしてください。',
                'frequency' => 0,
            ];
        }

        return $patterns;
    }

    /**
     * アドバイスを生成
     */
    private function generateAdvice(array $patterns, array $stats): array
    {
        $advice = [];

        // 完了率に基づくアドバイス
        if ($stats['completion_rate'] >= 80) {
            $advice[] = [
                'icon' => 'star',
                'type' => 'positive',
                'title' => '素晴らしい完了率！',
                'content' => '完了率 ' . $stats['completion_rate'] . '% は非常に優秀です。この調子で続けましょう！',
            ];
        } elseif ($stats['completion_rate'] >= 50) {
            $advice[] = [
                'icon' => 'chart-bar',
                'type' => 'neutral',
                'title' => '改善の余地あり',
                'content' => '完了率 ' . $stats['completion_rate'] . '% です。計画を小さく分割すると完了しやすくなります。',
            ];
        }

        // パターンに基づくアドバイス
        foreach ($patterns as $pattern) {
            if ($pattern['type'] === 'deadline_miss') {
                $advice[] = [
                    'icon' => 'calendar',
                    'type' => 'action',
                    'title' => '締め切り管理',
                    'content' => '締め切りの2日前に「中間チェックポイント」を設定すると、遅延を防げます。',
                ];
            }
            
            if ($pattern['type'] === 'high_skip_rate') {
                $advice[] = [
                    'icon' => 'scissors',
                    'type' => 'action',
                    'priority' => 'recommended',
                    'title' => '計画分割のすすめ',
                    'content' => '大きな計画は2時間以内の小計画に分割すると、完了率が大幅に向上します。',
                ];
            }
        }

        // デフォルトのアドバイス
        if ($stats['total'] < 5) {
            $advice = [
                [
                    'icon' => 'rocket-launch',
                    'type' => 'action',
                    'priority' => 'urgent',
                    'title' => 'まずは課題をインポート',
                    'content' => 'Backlogから課題を読み込んで、あなたの作業パターンを分析しましょう。',
                ],
                [
                    'icon' => 'chart-bar',
                    'type' => 'info',
                    'priority' => 'recommended',
                    'title' => 'AI分析について',
                    'content' => '5件以上のデータがあれば、失敗パターンや進捗の癖を分析できます。',
                ],
                [
                    'icon' => 'light-bulb',
                    'type' => 'tip',
                    'priority' => 'reference',
                    'title' => 'ヒント',
                    'content' => 'AI計画生成を使って、効率的な学習スケジュールを自動作成しましょう。',
                ],
            ];
        }

        return $advice;
    }

    /**
     * 週間データを取得
     */
    private function getWeeklyData(int $userId): array
    {
        $startOfWeek = now()->startOfWeek();
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayPlans = StudyPlan::where('user_id', $userId)
                ->whereDate('scheduled_date', $date)
                ->get();
            
            $data[] = [
                'day' => $date->isoFormat('ddd'),
                'date' => $date->format('m/d'),
                'completed' => $dayPlans->where('status', 'completed')->count(),
                'failed' => $dayPlans->where('status', 'skipped')->count(),
                'dayOfWeek' => $date->dayOfWeek,
            ];
        }

        return $data;
    }
}
