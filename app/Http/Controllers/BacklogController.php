<?php

namespace App\Http\Controllers;

use App\Models\BacklogSetting;
use App\Models\ImportedIssue;
use App\Services\BacklogApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BacklogController extends Controller
{
    public function __construct(
        private readonly BacklogApiService $backlogService
    ) {}

    /**
     * Backlog設定画面表示
     */
    public function settings(): View
    {
        $setting = BacklogSetting::firstOrNew(['user_id' => Auth::id()]);
        
        // サービスからプロジェクト一覧を取得
        $projects = [];
        $apiError = null;
        
        try {
            $projects = $this->backlogService->getProjects();
        } catch (\Exception $e) {
            $apiError = 'Backlog APIへの接続に失敗しました: ' . $e->getMessage();
        }

        return view('backlog.settings', compact('setting', 'projects', 'apiError'));
    }

    /**
     * 設定を保存
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'space_url' => ['required', 'url'],
            'api_key' => ['required', 'string'],
            'selected_project_id' => ['nullable', 'string'],
        ]);

        $setting = BacklogSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'space_url' => $validated['space_url'],
                'api_key' => $validated['api_key'],
                'selected_project_id' => $validated['selected_project_id'] ?? null,
                'selected_project_name' => $request->input('selected_project_name'),
                'is_connected' => true,
                'last_synced_at' => now(),
            ]
        );

        return redirect()->route('backlog.settings')
            ->with('success', 'Backlog設定を保存しました');
    }

    /**
     * プロジェクト一覧取得
     */
    public function projects(): View
    {
        try {
            $projects = $this->backlogService->getProjects();
        } catch (\Exception $e) {
            $projects = [];
        }
        return view('backlog.projects', compact('projects'));
    }

    /**
     * 課題一覧表示
     */
    public function issues(Request $request): View
    {
        $setting = BacklogSetting::where('user_id', Auth::id())->first();
        
        // サービスから課題一覧を取得
        $backlogIssues = [];
        $apiError = null;
        
        try {
            $backlogIssues = $this->backlogService->getIssues();
        } catch (\Exception $e) {
            $apiError = 'Backlog APIへの接続に失敗しました: ' . $e->getMessage();
        }
        
        // インポート済み課題のIDを取得
        $importedIssueIds = ImportedIssue::where('user_id', Auth::id())
            ->pluck('backlog_issue_id')
            ->toArray();

        // フィルター適用
        if ($request->filled('status')) {
            $backlogIssues = array_filter($backlogIssues, function ($issue) use ($request) {
                return $issue['status']['name'] === $request->status;
            });
        }

        if ($request->filled('priority')) {
            $backlogIssues = array_filter($backlogIssues, function ($issue) use ($request) {
                return $issue['priority']['name'] === $request->priority;
            });
        }

        // インポート済み課題の一覧
        $importedIssues = ImportedIssue::where('user_id', Auth::id())
            ->orderBy('due_date')
            ->get();

        return view('backlog.issues', compact('backlogIssues', 'importedIssueIds', 'importedIssues', 'setting', 'apiError'));
    }

    /**
     * 課題をインポート
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'issue_ids' => ['required', 'array'],
            'issue_ids.*' => ['integer'],
        ]);

        $mockIssues = collect($this->backlogService->getIssues())->keyBy('id');
        $imported = 0;

        foreach ($validated['issue_ids'] as $issueId) {
            if (!$mockIssues->has($issueId)) {
                continue;
            }

            $issue = $mockIssues[$issueId];
            
            ImportedIssue::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'backlog_issue_id' => $issue['id'],
                ],
                [
                    'issue_key' => $issue['issueKey'],
                    'summary' => $issue['summary'],
                    'description' => $issue['description'] ?? null,
                    'issue_type' => $issue['issueType']['name'] ?? null,
                    'issue_type_color' => $issue['issueType']['color'] ?? null,
                    'priority' => $issue['priority']['name'] ?? null,
                    'status' => $issue['status']['name'] ?? null,
                    'status_color' => $issue['status']['color'] ?? null,
                    'due_date' => $issue['dueDate'] ?? null,
                    'start_date' => $issue['startDate'] ?? null,
                    'estimated_hours' => $issue['estimatedHours'] ?? null,
                    'actual_hours' => $issue['actualHours'] ?? null,
                    'milestone' => $issue['milestone'][0]['name'] ?? null,
                    'assignee_name' => $issue['assignee']['name'] ?? null,
                    'backlog_url' => "https://example.backlog.com/view/{$issue['issueKey']}",
                ]
            );
            $imported++;
        }

        return redirect()->route('backlog.issues')
            ->with('success', "{$imported}件の課題をインポートしました");
    }
}

