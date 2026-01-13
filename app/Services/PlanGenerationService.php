<?php

namespace App\Services;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * 計画生成サービス
 * 
 * 計画生成に関するビジネスロジックを集約するサービスクラス
 */
class PlanGenerationService
{
    /**
     * 未消化の課題を取得
     */
    public function getPendingIssues(int $userId): Collection
    {
        return ImportedIssue::where('user_id', $userId)
            ->whereNotIn('status', ['完了', '処理済み'])
            ->orderByRaw("CASE 
                WHEN priority = '高' THEN 1 
                WHEN priority = '中' THEN 2 
                ELSE 3 
            END")
            ->orderBy('due_date')
            ->get();
    }

    /**
     * 既存の計画をクリア（再生成時に重複を防ぐ）
     * 今日以降の全ステータスの計画を削除（完了済みも含む）
     */
    public function clearPendingPlans(int $userId): void
    {
        StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', today())
            ->delete();
    }

    /**
     * 課題から計画を生成
     */
    public function generatePlans(int $userId, Collection $issues): void
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
    public function generateAiReason($issue, bool $isFirst): string
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
     * 生成された計画をフォーマットして返す
     */
    public function getFormattedPlans(int $userId): array
    {
        $plans = StudyPlan::where('user_id', $userId)
            ->where('scheduled_date', '>=', today())
            ->with('importedIssue')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        // 数値優先度を日本語文字列に変換
        $priorityMap = [
            9 => '高',
            5 => '中',
            3 => '低',
        ];

        return $plans->map(function ($plan) use ($priorityMap) {
            return [
                'id' => $plan->id,
                'issue_key' => $plan->importedIssue?->issue_key ?? null,
                'title' => $plan->title,
                'planned_minutes' => $plan->duration_minutes,
                'priority' => $priorityMap[$plan->priority] ?? '中',
                'ai_comment' => $plan->ai_reason,
            ];
        })->toArray();
    }
}
