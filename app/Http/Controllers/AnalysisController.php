<?php

namespace App\Http\Controllers;

use App\Models\ImportedIssue;
use App\Models\StudyPlan;
use App\Services\AnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly AnalysisService $analysisService
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
            return $this->analysisService->generateApiAdvice($userId, $targetDate);
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
}
