<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Backend API Service
 * 
 * バックエンドAPI (backlog_demo) との通信を担当するサービスクラス。
 * API呼び出し失敗時はローカルサービスにフォールバック。
 */
class BackendApiService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;
    protected bool $fallbackEnabled;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('backend_api.base_url', 'http://localhost:8080'), '/');
        $this->timeout = config('backend_api.timeout', 30);
        $this->retryTimes = config('backend_api.retry_times', 2);
        $this->retrySleep = config('backend_api.retry_sleep', 100);
        $this->fallbackEnabled = config('backend_api.fallback_enabled', true);
    }

    /**
     * AI計画生成をバックエンドAPIに依頼
     * 
     * @param array $issues 課題データの配列（フロントエンドのImportedIssueから整形）
     * @return array|null 成功時はレスポンスデータ、失敗時はnull
     */
    public function generatePlanning(array $issues = []): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->post("{$this->baseUrl}/api/planning/generate", [
                    'issues' => $issues,
                ]);

            if ($response->successful()) {
                Log::info('Backend API: Planning generation successful');
                return $response->json();
            }

            Log::warning('Backend API: Planning generation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Planning generation error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/planning/generate",
            ]);

            return null;
        }
    }

    /**
     * AI分析アドバイスをバックエンドAPIに依頼
     * 
     * @param array $summary 統計データ
     * @return array|null 成功時はレスポンスデータ、失敗時はnull
     */
    public function generateAdvice(array $summary): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->post("{$this->baseUrl}/api/analysis/advice", [
                    'summary' => $summary,
                ]);

            if ($response->successful()) {
                Log::info('Backend API: Analysis advice successful');
                return $response->json();
            }

            Log::warning('Backend API: Analysis advice failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Analysis advice error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/analysis/advice",
            ]);

            return null;
        }
    }

    /**
     * バックエンドAPIが利用可能かチェック
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/planning/generate");
            // 405はエンドポイント存在を示す（GETは許可されていない）
            return $response->status() !== 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * フォールバックが有効かどうか
     */
    public function isFallbackEnabled(): bool
    {
        return $this->fallbackEnabled;
    }

    /**
     * 未消化の課題リストを取得（サイドバー用）
     * 
     * Backlogからインポート済みだが、まだ「今日の計画（daily_plans）」に
     * 登録されていない課題の一覧を取得する。
     * 
     * @return array|null 成功時は課題リスト、失敗時はnull
     */
    public function getUnscheduledIssues(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/api/planning/unscheduled");

            if ($response->successful()) {
                Log::info('Backend API: Unscheduled issues fetched successfully');
                return $response->json();
            }

            Log::warning('Backend API: Unscheduled issues fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Unscheduled issues error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/planning/unscheduled",
            ]);

            return null;
        }
    }

    /**
     * 今日のタスクボード取得（カンバン用）
     * 
     * カンバンボードの初期表示に使用。「予定」「進行中」「完了」「スキップ」
     * などのレーンごとに整理されたJSONを返す。
     * 
     * @param string|null $date 日付 (Y-m-d形式、nullの場合は今日)
     * @return array|null 成功時はレーン別データ、失敗時はnull
     */
    public function getDailyPlanning(?string $date = null): ?array
    {
        try {
            $params = [];
            if ($date) {
                $params['date'] = $date;
            }
            
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/api/planning/daily", $params);

            if ($response->successful()) {
                Log::info('Backend API: Daily planning fetched successfully');
                return $response->json();
            }

            Log::warning('Backend API: Daily planning fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Daily planning error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/planning/daily",
            ]);

            return null;
        }
    }

    /**
     * 分析サマリーを取得
     * 
     * @param string|null $date 日付 (Y-m-d形式、nullの場合は今日)
     * @return array|null 成功時はサマリーデータ、失敗時はnull
     */
    public function getSummary(?string $date = null): ?array
    {
        try {
            $params = [];
            if ($date) {
                $params['date'] = $date;
            }
            
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/api/analysis/summary", $params);

            if ($response->successful()) {
                Log::info('Backend API: Analysis summary fetched successfully');
                return $response->json();
            }

            Log::warning('Backend API: Analysis summary fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Analysis summary error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/analysis/summary",
            ]);

            return null;
        }
    }

    /**
     * 週間進捗データを取得
     * 
     * @param string|null $date 日付 (Y-m-d形式、nullの場合は今日)
     * @return array|null 成功時は週間データ、失敗時はnull
     */
    public function getWeeklyProgress(?string $date = null): ?array
    {
        try {
            $params = [];
            if ($date) {
                $params['date'] = $date;
            }
            
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/api/analysis/weekly-progress", $params);

            if ($response->successful()) {
                Log::info('Backend API: Weekly progress fetched successfully');
                return $response->json();
            }

            Log::warning('Backend API: Weekly progress fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Weekly progress error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/analysis/weekly-progress",
            ]);

            return null;
        }
    }

    /**
     * カテゴリ別統計を取得
     * 
     * @param string|null $date 日付 (Y-m-d形式、nullの場合は今日)
     * @return array|null 成功時はカテゴリデータ、失敗時はnull
     */
    public function getCategories(?string $date = null): ?array
    {
        try {
            $params = [];
            if ($date) {
                $params['date'] = $date;
            }
            
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/api/analysis/categories", $params);

            if ($response->successful()) {
                Log::info('Backend API: Categories fetched successfully');
                return $response->json();
            }

            Log::warning('Backend API: Categories fetch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Categories error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/analysis/categories",
            ]);

            return null;
        }
    }

    /**
     * タスクステータスを更新（バックエンドに同期）
     * 
     * @param int $taskId タスクID
     * @param string $status 新しいステータス
     * @return array|null 成功時はレスポンスデータ、失敗時はnull
     */
    public function updateTaskStatus(int $taskId, string $status): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->patch("{$this->baseUrl}/api/planning/tasks/{$taskId}/status", [
                    'status' => $status,
                ]);

            if ($response->successful()) {
                Log::info('Backend API: Task status updated successfully', [
                    'task_id' => $taskId,
                    'status' => $status,
                ]);
                return $response->json();
            }

            Log::warning('Backend API: Task status update failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Backend API: Task status update error', [
                'error' => $e->getMessage(),
                'url' => "{$this->baseUrl}/api/planning/tasks/{$taskId}/status",
            ]);

            return null;
        }
    }
}

