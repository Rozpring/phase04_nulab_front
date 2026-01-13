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
     * @return array|null 成功時はレスポンスデータ、失敗時はnull
     */
    public function generatePlanning(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->post("{$this->baseUrl}/api/planning/generate");

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
}
