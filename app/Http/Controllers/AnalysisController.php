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
     * バックエンドAPIを優先し、失敗時はローカルサービスにフォールバック
     */
    public function index(): View
    {
        $userId = Auth::id();

        // バックエンドAPIから統計データを取得を試行
        $backendSummary = $this->backendApi->getSummary();
        $backendWeekly = $this->backendApi->getWeeklyProgress();
        $backendCategories = $this->backendApi->getCategories();

        // バックエンドが成功した場合はそのデータを使用
        if ($backendSummary && $backendWeekly) {
            $stats = [
                'total' => $backendSummary['total_tasks'] ?? 0,
                'completed' => 0, // バックエンドからは直接取得できないため計算
                'failed' => 0,
                'in_progress' => $backendSummary['in_progress'] ?? 0,
                'completion_rate' => $backendSummary['completion_rate'] ?? 0,
                'failure_rate' => $backendSummary['failure_rate'] ?? 0,
                'by_category' => $this->formatBackendCategories($backendCategories ?? []),
            ];
            
            $weeklyData = $this->formatBackendWeeklyData($backendWeekly);
            
            // パターン検出とアドバイスはローカルで生成（バックエンドにない機能）
            $issues = ImportedIssue::where('user_id', $userId)->get();
            $plans = StudyPlan::with('importedIssue')->where('user_id', $userId)->get();
            $patterns = $this->analysisService->detectPatterns($issues, $plans);
            $advice = $this->analysisService->generateAdvice($patterns, $stats);
            
            return view('analysis.index', compact('stats', 'patterns', 'advice', 'weeklyData'));
        }

        // フォールバック: ローカルサービスで生成
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
     * バックエンドのカテゴリデータをフロントエンド形式に変換
     */
    private function formatBackendCategories(array $categories): array
    {
        $result = [
            'study' => ['total' => 0, 'completed' => 0, 'rate' => 0],
            'work' => ['total' => 0, 'completed' => 0, 'rate' => 0],
            'personal' => ['total' => 0, 'completed' => 0, 'rate' => 0],
        ];

        foreach ($categories as $category) {
            $name = strtolower($category['name'] ?? '');
            if ($name === '学校' || $name === '学習' || $name === 'study') {
                $key = 'study';
            } elseif ($name === '仕事' || $name === 'work') {
                $key = 'work';
            } else {
                $key = 'personal';
            }
            
            $result[$key] = [
                'total' => ($result[$key]['total'] ?? 0) + ($category['total'] ?? 0),
                'completed' => ($result[$key]['completed'] ?? 0) + ($category['completed'] ?? 0),
                'rate' => $category['completion_rate'] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * バックエンドの週間データをフロントエンド形式に変換
     */
    private function formatBackendWeeklyData(array $weeklyData): array
    {
        $dayLabels = ['日', '月', '火', '水', '木', '金', '土'];
        
        return array_map(function ($day) use ($dayLabels) {
            $date = \Carbon\Carbon::parse($day['date']);
            return [
                'day' => $dayLabels[$date->dayOfWeek],
                'date' => $day['date'],
                'completed' => $day['completed'] ?? 0,
                'failed' => $day['failed'] ?? 0,
            ];
        }, $weeklyData);
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

    /**
     * 分析サマリーAPI（プロキシ）
     * バックエンドAPIを呼び出し、失敗時はローカルで生成
     */
    public function apiSummary(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $targetDate = $request->input('date', today()->format('Y-m-d'));

        // バックエンドAPIを試行
        $backendResponse = $this->backendApi->getSummary($targetDate);
        
        if ($backendResponse) {
            return response()->json([
                'success' => true,
                'source' => 'backend_api',
                'data' => $backendResponse,
            ]);
        }

        // フォールバック: ローカルサービスで生成
        $issues = ImportedIssue::where('user_id', $userId)->get();
        $plans = StudyPlan::where('user_id', $userId)->get();
        $stats = $this->analysisService->calculateStats($issues, $plans);

        return response()->json([
            'success' => true,
            'source' => 'local_fallback',
            'data' => [
                'total_tasks' => $stats['total'],
                'completion_rate' => $stats['completion_rate'],
                'in_progress' => $stats['in_progress'],
                'failure_rate' => $stats['failure_rate'],
                'period' => $targetDate,
            ],
        ]);
    }

    /**
     * 週間進捗API（プロキシ）
     * バックエンドAPIを呼び出し、失敗時はローカルで生成
     */
    public function apiWeeklyProgress(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $targetDate = $request->input('date', today()->format('Y-m-d'));

        // バックエンドAPIを試行
        $backendResponse = $this->backendApi->getWeeklyProgress($targetDate);
        
        if ($backendResponse) {
            return response()->json([
                'success' => true,
                'source' => 'backend_api',
                'data' => $backendResponse,
            ]);
        }

        // フォールバック: ローカルサービスで生成
        $weeklyData = $this->analysisService->getWeeklyData($userId);

        return response()->json([
            'success' => true,
            'source' => 'local_fallback',
            'data' => $weeklyData,
        ]);
    }

    /**
     * カテゴリ別統計API（プロキシ）
     * バックエンドAPIを呼び出し、失敗時はローカルで生成
     */
    public function apiCategories(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $targetDate = $request->input('date', today()->format('Y-m-d'));

        // バックエンドAPIを試行
        $backendResponse = $this->backendApi->getCategories($targetDate);
        
        if ($backendResponse) {
            return response()->json([
                'success' => true,
                'source' => 'backend_api',
                'data' => $backendResponse,
            ]);
        }

        // フォールバック: ローカルサービスで生成
        $issues = ImportedIssue::where('user_id', $userId)->get();
        $plans = StudyPlan::where('user_id', $userId)->get();
        $stats = $this->analysisService->calculateStats($issues, $plans);

        $categories = [];
        foreach ($stats['by_category'] as $name => $data) {
            $categories[] = [
                'name' => $name,
                'total' => $data['total'],
                'completed' => $data['completed'],
                'completion_rate' => $data['rate'],
            ];
        }

        return response()->json([
            'success' => true,
            'source' => 'local_fallback',
            'data' => $categories,
        ]);
    }
}

