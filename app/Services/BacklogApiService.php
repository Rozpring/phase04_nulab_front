<?php

namespace App\Services;

use App\Models\BacklogSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Backlog API Service
 * 
 * Backlog APIとの通信を担当するサービスクラス。
 * レートリミット対応、ページネーション処理を含む。
 * 
 * 認証情報はデータベース(BacklogSetting)から優先的に読み込み、
 * 未設定の場合は.env(config/backlog.php)をフォールバックとして使用。
 */
class BacklogApiService
{
    protected ?string $spaceUrl;
    protected ?string $apiKey;
    protected int $timeout;
    protected int $retryAfter;
    protected int $maxRetries;
    protected int $paginationCount;

    public function __construct()
    {
        // データベースから認証情報を取得（ログイン中のユーザー）
        $this->loadCredentials();
        
        // その他の設定はconfigから
        $this->timeout = config('backlog.timeout', 30);
        $this->retryAfter = config('backlog.rate_limit.retry_after', 60);
        $this->maxRetries = config('backlog.rate_limit.max_retries', 3);
        $this->paginationCount = config('backlog.pagination.count', 100);
    }

    /**
     * 認証情報をロード（DB優先、configフォールバック）
     */
    protected function loadCredentials(): void
    {
        $this->spaceUrl = null;
        $this->apiKey = null;

        // 認証済みユーザーがいる場合、DBから取得
        if (Auth::check()) {
            $setting = BacklogSetting::where('user_id', Auth::id())->first();
            if ($setting && $setting->space_url && $setting->api_key) {
                $this->spaceUrl = rtrim($setting->space_url, '/');
                $this->apiKey = $setting->api_key;
                return;
            }
        }

        // フォールバック: configから取得
        $configUrl = config('backlog.space_url');
        $configKey = config('backlog.api_key');
        
        if ($configUrl && $configKey) {
            $this->spaceUrl = rtrim($configUrl, '/');
            $this->apiKey = $configKey;
        }
    }

    /**
     * 認証情報を再読み込み（設定保存後に呼び出す用）
     */
    public function refreshCredentials(): void
    {
        $this->loadCredentials();
    }

    /**
     * API認証情報が設定されているかチェック
     */
    protected function ensureConfigured(): void
    {
        // 最新の認証情報を取得
        $this->loadCredentials();
        
        if (empty($this->spaceUrl) || empty($this->apiKey)) {
            throw new Exception('Backlog APIの認証情報が設定されていません。Backlog連携設定画面からスペースURLとAPIキーを設定してください。');
        }
    }

    /**
     * 課題一覧を取得（差分更新対応、プロジェクトフィルタ対応）
     *
     * @param string|null $updatedSince 更新日時フィルタ (yyyy-MM-dd形式)
     * @param int|null $projectId プロジェクトIDでフィルタリング
     * @return array
     */
    public function getIssues(?string $updatedSince = null, ?int $projectId = null): array
    {
        $this->ensureConfigured();

        $allIssues = [];
        $offset = 0;

        do {
            $params = [
                'apiKey' => $this->apiKey,
                'count' => $this->paginationCount,
                'offset' => $offset,
            ];

            // プロジェクトIDでフィルタリング
            if ($projectId) {
                $params['projectId[]'] = $projectId;
            }

            if ($updatedSince) {
                $params['updatedSince'] = $updatedSince;
            }

            $issues = $this->makeRequest('GET', '/api/v2/issues', $params);

            if (empty($issues)) {
                break;
            }

            $allIssues = array_merge($allIssues, $issues);
            $offset += $this->paginationCount;

            // レートリミット対策: 1秒待機
            sleep(1);

        } while (count($issues) === $this->paginationCount);

        return $allIssues;
    }

    /**
     * 課題の詳細を取得
     *
     * @param string $issueIdOrKey 課題IDまたはキー
     * @return array
     */
    public function getIssue(string $issueIdOrKey): array
    {
        $this->ensureConfigured();

        return $this->makeRequest('GET', "/api/v2/issues/{$issueIdOrKey}", [
            'apiKey' => $this->apiKey,
        ]);
    }

    /**
     * IDで課題を取得（BacklogMockService互換）
     *
     * @param int $id 課題ID
     * @return array|null
     */
    public function findIssueById(int $id): ?array
    {
        try {
            return $this->getIssue((string) $id);
        } catch (Exception $e) {
            Log::warning('Failed to find issue by ID', ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 課題をIDリストで取得（BacklogMockService互換）
     *
     * @param array $ids 課題IDの配列
     * @return array
     */
    public function getIssuesByIds(array $ids): array
    {
        $issues = [];
        foreach ($ids as $id) {
            $issue = $this->findIssueById($id);
            if ($issue) {
                $issues[] = $issue;
            }
        }
        return $issues;
    }

    /**
     * レートリミット情報を取得
     *
     * @return array
     */
    public function getRateLimit(): array
    {
        $this->ensureConfigured();

        return $this->makeRequest('GET', '/api/v2/rateLimit', [
            'apiKey' => $this->apiKey,
        ]);
    }

    /**
     * HTTP リクエストを実行（レートリミット対応）
     *
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @param int $retryCount
     * @return array
     * @throws Exception
     */
    protected function makeRequest(string $method, string $endpoint, array $params = [], int $retryCount = 0): array
    {
        $url = $this->spaceUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->$method($url, $params);

            // レートリミット情報をログ出力
            $this->logRateLimitHeaders($response->headers());

            if ($response->status() === 429) {
                return $this->handleRateLimitError($method, $endpoint, $params, $retryCount);
            }

            if ($response->failed()) {
                throw new Exception("Backlog API request failed: {$response->status()} - {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Backlog API request error', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 429エラー（レートリミット超過）の処理
     *
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @param int $retryCount
     * @return array
     * @throws Exception
     */
    protected function handleRateLimitError(string $method, string $endpoint, array $params, int $retryCount): array
    {
        if ($retryCount >= $this->maxRetries) {
            throw new Exception("Rate limit exceeded. Max retries ({$this->maxRetries}) reached.");
        }

        $waitTime = $this->retryAfter;
        Log::warning("Rate limit exceeded. Retrying after {$waitTime} seconds... (Attempt: " . ($retryCount + 1) . "/{$this->maxRetries})");

        sleep($waitTime);

        return $this->makeRequest($method, $endpoint, $params, $retryCount + 1);
    }

    /**
     * レートリミットヘッダーをログ出力
     *
     * @param array $headers
     * @return void
     */
    protected function logRateLimitHeaders(array $headers): void
    {
        $rateLimitInfo = [
            'limit' => $headers['X-RateLimit-Limit'][0] ?? null,
            'remaining' => $headers['X-RateLimit-Remaining'][0] ?? null,
            'reset' => $headers['X-RateLimit-Reset'][0] ?? null,
        ];

        if ($rateLimitInfo['limit']) {
            Log::debug('Backlog API Rate Limit', $rateLimitInfo);
        }
    }

    /**
     * プロジェクト一覧を取得
     *
     * @return array
     */
    public function getProjects(): array
    {
        $this->ensureConfigured();

        return $this->makeRequest('GET', '/api/v2/projects', [
            'apiKey' => $this->apiKey,
        ]);
    }

    /**
     * 課題タイプ一覧を取得
     *
     * @param int $projectId
     * @return array
     */
    public function getIssueTypes(int $projectId): array
    {
        $this->ensureConfigured();

        return $this->makeRequest('GET', "/api/v2/projects/{$projectId}/issueTypes", [
            'apiKey' => $this->apiKey,
        ]);
    }

    /**
     * 優先度一覧を取得
     *
     * @return array
     */
    public function getPriorities(): array
    {
        $this->ensureConfigured();

        return $this->makeRequest('GET', '/api/v2/priorities', [
            'apiKey' => $this->apiKey,
        ]);
    }

    /**
     * 課題を作成
     *
     * @param array $data
     * @return array
     */
    public function createIssue(array $data): array
    {
        $this->ensureConfigured();

        // APIキーはクエリパラメータとして送る
        $url = $this->spaceUrl . '/api/v2/issues?apiKey=' . $this->apiKey;

        try {
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($url, $data);

            $this->logRateLimitHeaders($response->headers());

            if ($response->status() === 429) {
                return $this->handleRateLimitError('POST', '/api/v2/issues', $data, 0);
            }

            if ($response->failed()) {
                throw new Exception("Failed to create issue: {$response->status()} - {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Failed to create Backlog issue', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }
}
