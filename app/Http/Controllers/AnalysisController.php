<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Services\AnalysisService;
use App\Services\BackendApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly AnalysisService $analysisService,
        private readonly BackendApiService $backendApi
    ) {}


    /**
     * AI分析ダッシュボード
     */
    public function index(): View
    {
        $userId = Auth::id();

        $issues = ImportedIssue::where('user_id', $userId)->get();
        $plans = StudyPlan::with('importedIssue')
            ->where('user_id', $userId)
            ->get();
        
        $stats = $this->analysisService->calculateStats($issues, $plans);
        $patterns = $this->analysisService->detectPatterns($issues, $plans);
        $advice = $this->analysisService->generateAdvice($patterns, $stats);
        $weeklyData = $this->analysisService->getWeeklyData($userId);

        return view('analysis.index', compact('stats', 'patterns', 'advice', 'weeklyData'));
    }

    /**
     * 週間・月間レポート
     */
    public function report(): View
    {
        return view('analysis.report');
    }

    /**
     * AI分析アドバイスAPI
     * バックエンドAPIを優先し、失敗時はローカルサービスにフォールバック
     */
    public function apiAdvice(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $targetDate = $request->input('date', today()->format('Y-m-d'));
        $refresh = $request->boolean('refresh', false);
        
        $cacheKey = "analysis_advice_{$userId}_{$targetDate}";
        
        if ($refresh) {
            Cache::forget($cacheKey);
        }
        
        $cached = Cache::has($cacheKey);
        
        $advice = Cache::remember($cacheKey, now()->addHours(1), function () use ($userId, $targetDate) {
            // 統計データを準備
            $issues = ImportedIssue::where('user_id', $userId)->get();
            $plans = StudyPlan::where('user_id', $userId)->get();
            $stats = $this->analysisService->calculateStats($issues, $plans);
            
            // バックエンドAPIを試行
            $backendResponse = $this->backendApi->generateAdvice($stats);
            
            if ($backendResponse && isset($backendResponse['data']['advice'])) {
                return [
                    'advice' => $backendResponse['data']['advice'],
                    'source' => 'backend_api',
                ];
            }
            
            // フォールバック: ローカルサービスで生成
            if ($this->backendApi->isFallbackEnabled()) {
                return [
                    'advice' => $this->analysisService->generateApiAdvice($userId, $targetDate),
                    'source' => 'local_fallback',
                ];
            }
            
            return [
                'advice' => [],
                'source' => 'none',
            ];
        });
        
        return response()->json([
            'success' => true,
            'cached' => $cached && !request()->boolean('refresh'),
            'data' => [
                'target_date' => $targetDate,
                'advice' => $advice['advice'] ?? $advice,
                'source' => $advice['source'] ?? 'unknown',
            ],
        ]);
    }
}

