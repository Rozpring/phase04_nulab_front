<?php

namespace App\Services;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use Illuminate\Support\Collection;

/**
 * 分析サービス
 * 
 * 分析・アドバイス生成に関するビジネスロジックを集約するサービスクラス
 */
class AnalysisService
{
    /**
     * 統計情報を計算
     */
    public function calculateStats(Collection $issues, Collection $plans): array
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
    public function detectPatterns(Collection $issues, Collection $plans): array
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
    public function generateAdvice(array $patterns, array $stats): array
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
     * API用アドバイス生成
     */
    public function generateApiAdvice(int $userId, string $targetDate): array
    {
        $startDate = now()->subDays(7);
        $plans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
            ->where('scheduled_date', '>=', $startDate)
            ->get();
        
        // Backlogインポート課題を取得
        $issues = ImportedIssue::where('user_id', $userId)->get();
        
        $totalPlans = $plans->count();
        $completedPlans = $plans->where('status', 'completed')->count();
        $skippedPlans = $plans->where('status', 'skipped')->count();
        $plannedPlans = $plans->where('status', 'planned')->count();
        
        // Backlog課題の分析
        $totalIssues = $issues->count();
        $overdueIssues = $issues->filter(fn($i) => $i->is_overdue)->count();
        $highPriorityIssues = $issues->where('priority', '高')->count();
        $unhandledIssues = $issues->where('status', '未対応')->count();
        $dueSoonIssues = $issues->filter(fn($i) => $i->days_until_due !== null && $i->days_until_due >= 0 && $i->days_until_due <= 3)->count();
        
        $advice = [];
        
        // 【Backlog分析】締め切り超過課題がある場合（最優先）
        if ($overdueIssues > 0) {
            $advice[] = [
                'title' => '締め切り超過の課題があります',
                'description' => "{$overdueIssues}件の課題が締め切りを過ぎています。優先的に対応するか、期限を再設定しましょう。",
                'tag' => '緊急',
                'type' => 'warning',
            ];
        }
        
        // 【Backlog分析】締め切りが近い課題
        if ($dueSoonIssues > 0) {
            $advice[] = [
                'title' => '締め切りが近い課題',
                'description' => "{$dueSoonIssues}件の課題が3日以内に締め切りを迎えます。計画に組み込んで対応しましょう。",
                'tag' => '緊急',
                'type' => 'warning',
            ];
        }
        
        // 【Backlog分析】高優先度の未対応課題
        if ($highPriorityIssues > 0 && $unhandledIssues > 0) {
            $highUnhandled = $issues->where('priority', '高')->where('status', '未対応')->count();
            if ($highUnhandled > 0) {
                $advice[] = [
                    'title' => '高優先度の課題が未対応',
                    'description' => "優先度「高」の課題が{$highUnhandled}件未対応です。今日の計画に最優先で追加しましょう。",
                    'tag' => '緊急',
                    'type' => 'warning',
                ];
            }
        }
        
        // 【Backlog分析】未対応の課題がある場合（高優先度以外も含む）
        if ($unhandledIssues > 0) {
            $advice[] = [
                'title' => '未対応のBacklog課題',
                'description' => "{$unhandledIssues}件のBacklog課題が未対応です。優先度を確認して計画に組み込みましょう。",
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【計画分析】未着手タスクがある場合
        if ($plannedPlans > 0) {
            $advice[] = [
                'title' => '未着手タスクへの着手',
                'description' => "登録された{$plannedPlans}件のタスクが未着手状態です。まずは1件だけでも着手し、タスクの状態を更新する習慣をつけましょう。",
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【計画分析】完了タスクがない場合
        if ($completedPlans === 0 && $totalPlans > 0) {
            $advice[] = [
                'title' => 'タスクの細分化と完了体験',
                'description' => '完了タスクがないため、タスクが大きすぎる可能性があります。小さく分割し、短時間で完了できるタスクから取り組み、達成感を増やしましょう。',
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【計画分析】スキップ率が高い場合
        if ($totalPlans > 0 && ($skippedPlans / $totalPlans) > 0.2) {
            $skipRate = round(($skippedPlans / $totalPlans) * 100);
            $advice[] = [
                'title' => '計画の見直し',
                'description' => "スキップ率が{$skipRate}%と高めです。見積もり時間を短くするか、タスクを分割して取り組みやすくしましょう。",
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【Backlog分析】課題がインポートされていない場合
        if ($totalIssues === 0) {
            $advice[] = [
                'title' => 'Backlogから課題をインポート',
                'description' => 'Backlogの課題をインポートすると、締め切りや優先度に基づいたより正確なアドバイスを提供できます。',
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【計画分析】データが少ない場合
        if ($totalPlans === 0 && $totalIssues > 0) {
            $advice[] = [
                'title' => 'AI計画生成を活用',
                'description' => "インポートされた{$totalIssues}件の課題から、AI計画生成で効率的なスケジュールを作成しましょう。",
                'tag' => '推奨',
                'type' => 'recommend',
            ];
        }
        
        // 【良い結果】完了率が高い場合
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
            [
                'title' => '定期的な振り返り',
                'description' => '週に一度、完了したタスクと未完了のタスクを振り返り、次週の計画に活かしましょう。',
                'tag' => '参考',
                'type' => 'info',
            ],
        ];
        
        while (count($advice) < 3) {
            $advice[] = array_shift($defaultAdvice) ?? [
                'title' => '計画的なタスク管理',
                'description' => '毎朝10分、今日の優先タスクを確認する習慣をつけましょう。',
                'tag' => '参考',
                'type' => 'info',
            ];
        }
        
        return array_slice($advice, 0, 3);
    }

    /**
     * 週間データを取得
     */
    public function getWeeklyData(int $userId): array
    {
        $startOfWeek = now()->startOfWeek();
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayPlans = StudyPlan::with('importedIssue')
                ->where('user_id', $userId)
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
