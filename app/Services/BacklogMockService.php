<?php

namespace App\Services;

/**
 * Backlogモックデータサービス
 * 
 * 開発・デモ用のモックデータを提供します。
 * 将来的には実際のBacklog APIクライアントに置き換え可能。
 */
class BacklogMockService
{
    /**
     * モックプロジェクト一覧を取得
     */
    public function getProjects(): array
    {
        return [
            [
                'id' => 1,
                'projectKey' => 'STUDY',
                'name' => '学習プロジェクト',
                'description' => 'プログラミング学習用',
            ],
            [
                'id' => 2,
                'projectKey' => 'DEV',
                'name' => '開発プロジェクト',
                'description' => 'システム開発タスク',
            ],
        ];
    }

    /**
     * モック課題一覧を取得
     * 
     * 将来的にはBacklog APIから取得するよう置き換え
     */
    public function getIssues(): array
    {
        return [
            [
                'id' => 1,
                'issueKey' => 'STUDY-1',
                'summary' => 'Laravel認証機能を理解する',
                'description' => 'Laravel Breezeを使った認証機能の実装方法を学習する。',
                'issueType' => ['name' => 'タスク', 'color' => '#7ea800'],
                'priority' => ['name' => '高'],
                'status' => ['name' => '未対応', 'color' => '#ed8077'],
                'dueDate' => now()->addDays(2)->format('Y-m-d'),
                'estimatedHours' => 4,
                'milestone' => [['name' => 'Sprint 1']],
                'assignee' => ['name' => 'テストユーザー'],
            ],
            [
                'id' => 2,
                'issueKey' => 'STUDY-2',
                'summary' => 'Eloquent ORMの基礎を学ぶ',
                'description' => 'Eloquentを使ったデータベース操作の基本を習得。',
                'issueType' => ['name' => 'タスク', 'color' => '#7ea800'],
                'priority' => ['name' => '中'],
                'status' => ['name' => '処理中', 'color' => '#4488c5'],
                'dueDate' => now()->addDays(4)->format('Y-m-d'),
                'estimatedHours' => 3,
                'milestone' => [['name' => 'Sprint 1']],
                'assignee' => ['name' => 'テストユーザー'],
            ],
            [
                'id' => 3,
                'issueKey' => 'DEV-1',
                'summary' => 'ユーザー管理機能の実装',
                'description' => 'ユーザーの登録、編集、削除機能を実装。',
                'issueType' => ['name' => '機能', 'color' => '#2779bd'],
                'priority' => ['name' => '高'],
                'status' => ['name' => '処理中', 'color' => '#4488c5'],
                'dueDate' => now()->addDays(1)->format('Y-m-d'),
                'estimatedHours' => 8,
                'milestone' => [['name' => 'リリース v1.0']],
                'assignee' => ['name' => 'テストユーザー'],
            ],
        ];
    }

    /**
     * IDで課題を取得
     */
    public function findIssueById(int $id): ?array
    {
        $issues = collect($this->getIssues());
        return $issues->firstWhere('id', $id);
    }

    /**
     * 課題をIDリストで取得
     */
    public function getIssuesByIds(array $ids): array
    {
        $issues = collect($this->getIssues())->keyBy('id');
        return $issues->only($ids)->values()->toArray();
    }
}
