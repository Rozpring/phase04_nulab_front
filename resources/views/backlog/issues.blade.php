<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Backlog課題インポート') }}
            </h2>
            <a href="{{ route('backlog.settings') }}" class="text-lask-1 hover:opacity-80 text-sm font-medium flex items-center gap-1">
                <x-icon name="cog-6-tooth" class="w-4 h-4" />
                設定
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- フラッシュメッセージ --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-lask-success-light border border-lask-success text-lask-text-primary rounded-lg flex items-center gap-2">
                    <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0" />
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- 左側: Backlog課題一覧 --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <x-icon name="clipboard-document-list" class="w-6 h-6 mr-2 text-lask-1" />
                                Backlogの課題
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ count($backlogIssues) }}件
                            </span>
                        </div>

                        {{-- フィルター --}}
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <form method="GET" action="{{ route('backlog.issues') }}" class="flex flex-wrap gap-3">
                                <select name="status" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm">
                                    <option value="">すべてのステータス</option>
                                    <option value="未対応" {{ request('status') == '未対応' ? 'selected' : '' }}>未対応</option>
                                    <option value="処理中" {{ request('status') == '処理中' ? 'selected' : '' }}>処理中</option>
                                    <option value="完了" {{ request('status') == '完了' ? 'selected' : '' }}>完了</option>
                                </select>
                                <select name="priority" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm">
                                    <option value="">すべての優先度</option>
                                    <option value="高" {{ request('priority') == '高' ? 'selected' : '' }}>高</option>
                                    <option value="中" {{ request('priority') == '中' ? 'selected' : '' }}>中</option>
                                    <option value="低" {{ request('priority') == '低' ? 'selected' : '' }}>低</option>
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                                    絞り込み
                                </button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('backlog.import') }}" id="import-form">
                            @csrf
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($backlogIssues as $issue)
                                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <div class="flex items-start gap-4">
                                            <div class="flex items-center h-6">
                                                @if (in_array($issue['id'], $importedIssueIds))
                                                    <x-icon name="check-circle" class="w-5 h-5 text-emerald-500" title="インポート済み" />
                                                @else
                                                    <input 
                                                        type="checkbox" 
                                                        name="issue_ids[]" 
                                                        value="{{ $issue['id'] }}"
                                                        class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-lask-1 focus:ring-lask-accent"
                                                    >
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $issue['issueKey'] }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $issue['priority']['name'] === '高' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300' : '' }}
                                                        {{ $issue['priority']['name'] === '中' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : '' }}
                                                        {{ $issue['priority']['name'] === '低' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : '' }}
                                                    ">
                                                        {{ $issue['priority']['name'] }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                        style="background-color: {{ $issue['status']['color'] }}20; color: {{ $issue['status']['color'] }}">
                                                        {{ $issue['status']['name'] }}
                                                    </span>
                                                </div>
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $issue['summary'] }}</h4>
                                                @if (!empty($issue['description']))
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit($issue['description'], 100) }}</p>
                                                @endif
                                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    @if ($issue['dueDate'])
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                            期限: {{ $issue['dueDate'] }}
                                                        </span>
                                                    @endif
                                                    @if ($issue['estimatedHours'])
                                                        <span class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            見積: {{ $issue['estimatedHours'] }}h
                                                        </span>
                                                    @endif
                                                    @if (!empty($issue['milestone']))
                                                        <span class="flex items-center gap-1">
                                                            <x-icon name="flag" class="w-3.5 h-3.5" />
                                                            {{ $issue['milestone'][0]['name'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                        <x-icon name="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                                        <p>課題が見つかりませんでした</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- インポートボタン --}}
                            @if (count($backlogIssues) > 0)
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                            <input type="checkbox" id="select-all" class="rounded border-gray-300 dark:border-gray-600">
                                            すべて選択
                                        </label>
                                        <x-primary-button type="submit">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            選択した課題をインポート
                                        </x-primary-button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- 右側: インポート済み課題 --}}
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <x-icon name="check-circle" class="w-6 h-6 mr-2 text-emerald-500" />
                                インポート済み
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @forelse ($importedIssues as $issue)
                                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $issue->issue_key }}</span>
                                        @if ($issue->priority)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $issue->priority === '高' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300' : '' }}
                                                {{ $issue->priority === '中' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : '' }}
                                                {{ $issue->priority === '低' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : '' }}
                                            ">
                                                {{ $issue->priority }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $issue->status_color_class }}">
                                            {{ $issue->status }}
                                        </span>
                                        @if ($issue->is_overdue)
                                            <span class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-0.5">
                                                <x-icon name="exclamation-circle" class="w-3 h-3" />
                                                期限切れ
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">{{ $issue->summary }}</h4>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if ($issue->due_date)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $issue->due_date->year != now()->year ? $issue->due_date->format('Y/m/d') : $issue->due_date->format('m/d') }}
                                            </span>
                                        @endif
                                        @if ($issue->estimated_hours)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $issue->estimated_hours }}h
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    <p class="text-sm">まだインポートされた課題はありません</p>
                                </div>
                            @endforelse
                        </div>
                        @if ($importedIssues->count() > 0)
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('planning.index') }}" class="w-full text-center px-4 py-2 bg-lask-accent text-white rounded-lg hover:bg-lask-accent-hover transition font-medium flex items-center justify-center gap-2">
                                    <x-icon name="cpu-chip" class="w-4 h-4" />
                                    AI計画を生成
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- クイックリンク --}}
                    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl p-5 shadow-lg border border-lask-accent/30">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <x-icon name="rocket-launch" class="w-5 h-5 text-lask-1" />
                            次のステップ
                        </h4>
                        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 bg-lask-accent-subtle text-lask-1 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                <span>インポートしたい課題を選択</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 bg-lask-accent-subtle text-lask-1 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                <span>「インポート」ボタンをクリック</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 bg-lask-accent-subtle text-lask-1 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                <span>AI計画生成で学習スケジュールを作成</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="issue_ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</x-app-layout>
