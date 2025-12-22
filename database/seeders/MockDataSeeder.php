<?php

namespace Database\Seeders;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MockDataSeeder extends Seeder
{
    /**
     * 開発用モックデータを生成
     * 昨日と今日の日付のデータのみ作成
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('ユーザーが存在しません。先にユーザーを作成してください。');
            return;
        }

        foreach ($users as $user) {
            $this->createMockIssues($user->id);
            $this->createMockPlans($user->id);
        }
        
        $this->command->info('全ユーザー用のモックデータを作成しました！');
    }

    /**
     * モック課題を作成
     */
    private function createMockIssues(int $userId): void
    {
        $issues = [
            [
                'backlog_issue_id' => 1,
                'issue_key' => 'STUDY-1',
                'summary' => 'Laravel認証機能を理解する',
                'description' => 'Laravel Breezeを使った認証機能の実装方法を学習する。',
                'issue_type' => 'タスク',
                'issue_type_color' => '#7ea800',
                'priority' => '高',
                'status' => '未対応',
                'status_color' => '#ed8077',
                'due_date' => now()->addDays(2),
                'estimated_hours' => 4,
            ],
            [
                'backlog_issue_id' => 2,
                'issue_key' => 'STUDY-2',
                'summary' => 'Eloquent ORMの基礎を学ぶ',
                'description' => 'Eloquentを使ったデータベース操作の基本を習得。',
                'issue_type' => 'タスク',
                'issue_type_color' => '#7ea800',
                'priority' => '中',
                'status' => '処理中',
                'status_color' => '#4488c5',
                'due_date' => now()->addDays(4),
                'estimated_hours' => 3,
            ],
            [
                'backlog_issue_id' => 3,
                'issue_key' => 'DEV-1',
                'summary' => 'ユーザー管理機能の実装',
                'description' => 'ユーザーの登録、編集、削除、一覧表示機能を実装。',
                'issue_type' => '機能',
                'issue_type_color' => '#2779bd',
                'priority' => '高',
                'status' => '処理中',
                'status_color' => '#4488c5',
                'due_date' => now()->addDays(1),
                'estimated_hours' => 8,
            ],
        ];

        foreach ($issues as $issue) {
            ImportedIssue::updateOrCreate(
                ['user_id' => $userId, 'backlog_issue_id' => $issue['backlog_issue_id']],
                array_merge($issue, ['user_id' => $userId])
            );
        }
    }

    /**
     * モック計画を作成（昨日と今日のみ）
     */
    private function createMockPlans(int $userId): void
    {
        // 既存の計画をクリア
        StudyPlan::where('user_id', $userId)->delete();

        $plans = [
            // 昨日の計画（完了・スキップ済み）
            [
                'title' => 'Laravel認証の復習',
                'plan_type' => 'review',
                'scheduled_date' => now()->subDay(),
                'scheduled_time' => '09:00',
                'end_time' => '10:30',
                'duration_minutes' => 90,
                'priority' => 8,
                'status' => 'completed',
                'ai_reason' => '昨日の学習内容を定着させるため。',
            ],
            [
                'title' => 'API設計ドキュメント作成',
                'plan_type' => 'work',
                'scheduled_date' => now()->subDay(),
                'scheduled_time' => '11:00',
                'end_time' => '12:00',
                'duration_minutes' => 60,
                'priority' => 7,
                'status' => 'skipped',
                'ai_reason' => '優先度が高いがスキップされました。',
            ],
            // 今日の計画
            [
                'title' => 'Eloquent ORMの学習',
                'plan_type' => 'study',
                'scheduled_date' => now(),
                'scheduled_time' => '09:00',
                'end_time' => '11:00',
                'duration_minutes' => 120,
                'priority' => 9,
                'status' => 'completed',
                'ai_reason' => '午前中は集中力が高いため、難しいタスクを配置。',
            ],
            [
                'title' => '昼休み',
                'plan_type' => 'break',
                'scheduled_date' => now(),
                'scheduled_time' => '12:00',
                'end_time' => '13:00',
                'duration_minutes' => 60,
                'priority' => 10,
                'status' => 'planned',
                'ai_reason' => '午後の作業効率を維持するための休憩時間。',
            ],
            [
                'title' => 'ユーザー管理機能の実装',
                'plan_type' => 'work',
                'scheduled_date' => now(),
                'scheduled_time' => '13:00',
                'end_time' => '15:00',
                'duration_minutes' => 120,
                'priority' => 9,
                'status' => 'in_progress',
                'ai_reason' => '期限が明日のため緊急対応。',
            ],
            [
                'title' => '小休憩',
                'plan_type' => 'break',
                'scheduled_date' => now(),
                'scheduled_time' => '15:00',
                'end_time' => '15:15',
                'duration_minutes' => 15,
                'priority' => 10,
                'status' => 'planned',
                'ai_reason' => '集中力を維持するための短い休憩。',
            ],
            [
                'title' => 'コードレビュー対応',
                'plan_type' => 'work',
                'scheduled_date' => now(),
                'scheduled_time' => '15:15',
                'end_time' => '17:00',
                'duration_minutes' => 105,
                'priority' => 7,
                'status' => 'planned',
                'ai_reason' => 'バランスの取れたスケジュールのため配置。',
            ],
        ];

        foreach ($plans as $plan) {
            StudyPlan::create(array_merge($plan, [
                'user_id' => $userId,
                'scheduled_time' => Carbon::parse($plan['scheduled_date']->format('Y-m-d') . ' ' . $plan['scheduled_time']),
                'end_time' => Carbon::parse($plan['scheduled_date']->format('Y-m-d') . ' ' . $plan['end_time']),
            ]));
        }
    }
}
