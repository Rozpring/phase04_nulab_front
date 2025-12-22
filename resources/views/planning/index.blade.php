<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <x-icon name="target" class="w-6 h-6 text-lask-1" />
                {{ __('計画ダッシュボード') }}
            </h2>
            {{-- ビュー切替タブ --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <a href="{{ route('planning.index') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.index') ? 'bg-lask-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-lask-charcoal dark:text-gray-400 hover:text-lask-dark dark:hover:text-gray-200' }}">
                    <x-icon name="clipboard-document-list" class="w-4 h-4" />
                    カンバン
                </a>
                <a href="{{ route('planning.timeline') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.timeline') ? 'bg-lask-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-lask-charcoal dark:text-gray-400 hover:text-lask-dark dark:hover:text-gray-200' }}">
                    <x-icon name="clock" class="w-4 h-4" />
                    タイムライン
                </a>
                <a href="{{ route('planning.calendar') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.calendar') ? 'bg-lask-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-lask-charcoal dark:text-gray-400 hover:text-lask-dark dark:hover:text-gray-200' }}">
                    <x-icon name="calendar" class="w-4 h-4" />
                    カレンダー
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- フラッシュメッセージ --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-lask-success-light border border-lask-success text-lask-text-primary rounded-lg flex items-center gap-2 animate-pulse">
                    <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0 text-lask-success" />
                    {{ session('success') }}
                </div>
            @endif
            @if (session('warning'))
                <div class="mb-6 p-4 bg-lask-muted/30 dark:bg-lask-muted/10 border border-lask-muted text-lask-dark dark:text-lask-muted rounded-lg flex items-center gap-2">
                    <x-icon name="exclamation-triangle" class="w-5 h-5 flex-shrink-0" />
                    {{ session('warning') }}
                </div>
            @endif

            {{-- 統計カード --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 lg:p-6 shadow-sm">
                    <div class="text-3xl font-bold text-lask-text-primary">{{ $stats['pending_issues'] }}</div>
                    <div class="text-lask-text-secondary text-sm mt-1">未消化の課題</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 lg:p-6 shadow-sm">
                    <div class="text-3xl font-bold text-lask-text-primary">{{ $stats['today_plans'] }}</div>
                    <div class="text-lask-text-secondary text-sm mt-1">今日の計画</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 lg:p-6 shadow-sm">
                    <div class="text-3xl font-bold text-lask-text-primary">{{ number_format($stats['today_hours'], 1) }}h</div>
                    <div class="text-lask-text-secondary text-sm mt-1">今日の学習時間</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 lg:p-6 shadow-sm">
                    <div class="text-3xl font-bold text-lask-text-primary">{{ $stats['week_plans'] }}</div>
                    <div class="text-lask-text-secondary text-sm mt-1">今週の計画</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- 左側: 今日の計画 --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- AI計画生成 --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-lask-accent/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-lask-text-primary mb-2 flex items-center gap-2">
                                    <x-icon name="cpu-chip" class="w-6 h-6 text-lask-1" />
                                    AI計画生成
                                </h3>
                                <p class="text-lask-text-secondary text-sm">Backlogの課題から最適な学習スケジュールを自動生成します</p>
                            </div>
                            <form method="POST" action="{{ route('planning.generate') }}">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-lask-accent text-white rounded-xl font-bold hover:bg-lask-accent-hover transition shadow-lg flex items-center gap-2">
                                    <x-icon name="sparkles" class="w-5 h-5" />
                                    計画を生成
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- カンバンボード --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-lask-border">
                            <h3 class="text-lg font-semibold text-lask-text-primary flex items-center gap-2">
                                <x-icon name="clipboard-document-list" class="w-6 h-6 text-lask-1" />
                                タスクボード
                                <span class="ml-2 text-xs text-lask-text-secondary font-normal">（ドラッグ＆ドロップで移動）</span>
                            </h3>
                        </div>
                        <div class="p-4">
                            <x-kanban-board />
                        </div>
                    </div>


                    {{-- 週間プレビュー --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                        <div class="px-6 py-4 border-b border-lask-border">
                            <h3 class="text-lg font-semibold text-lask-text-primary flex items-center gap-2">
                                <x-icon name="chart-bar" class="w-6 h-6 text-lask-1" />
                                今週の予定
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-7 gap-3 sm:gap-4">
                                @for ($i = 0; $i < 7; $i++)
                                    @php
                                        $day = today()->addDays($i);
                                        $dayKey = $day->format('Y-m-d');
                                        $dayPlans = $weekPlans->get($dayKey, collect());
                                        $totalMinutes = $dayPlans->sum('duration_minutes');
                                    @endphp
                                    <div class="text-center relative group">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $day->isoFormat('ddd') }}</div>
                                        <div class="text-sm font-medium mb-2 {{ $day->isToday() ? 'w-6 h-6 mx-auto bg-lask-accent text-white rounded-full flex items-center justify-center' : 'text-gray-900 dark:text-gray-100' }}">{{ $day->day }}</div>
                                        <div class="h-28 bg-gray-100 dark:bg-gray-700 rounded-lg relative overflow-hidden cursor-pointer flex flex-col">
                                            <div class="flex-1 flex flex-col justify-start">
                                                @foreach ($dayPlans->take(5) as $plan)
                                                    @php
                                                        $colors = [
                                                            'study' => 'bg-lask-accent-subtle',
                                                            'work' => 'bg-[#6b8cae]',
                                                            'break' => 'bg-[#8fbc8f]',
                                                            'review' => 'bg-[#2c3e50]',
                                                        ];
                                                        // タスク時間に比例したバー高さを計算
                                                        // 15分 = 8px, 30分 = 12px, 60分 = 20px, 120分 = 32px
                                                        $durationMinutes = $plan->duration_minutes ?? 30;
                                                        $barHeight = max(8, min(32, round($durationMinutes / 4)));
                                                    @endphp
                                                    <div class="{{ $colors[$plan->plan_type] ?? 'bg-gray-500' }} mb-px flex-shrink-0 rounded-sm" style="height: {{ $barHeight }}px;"></div>
                                                @endforeach
                                            </div>
                                            @if ($dayPlans->count() > 5)
                                                <div class="absolute bottom-0 left-0 right-0 text-xs text-gray-500 dark:text-gray-400 text-center py-1 bg-gray-100/90 dark:bg-gray-700/90">
                                                    +{{ $dayPlans->count() - 5 }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $totalMinutes > 0 ? round($totalMinutes / 60, 1) . 'h' : '-' }}
                                        </div>
                                        
                                        {{-- ホバーツールチップ --}}
                                        @if ($dayPlans->count() > 0)
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-[9999]">
                                                <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap border border-gray-600" style="min-width: 160px;">
                                                    <div class="font-semibold mb-1.5 text-center border-b border-gray-600 pb-1">
                                                        {{ $day->isoFormat('M/D (ddd)') }}
                                                    </div>
                                                    <div class="space-y-1 max-h-32 overflow-y-auto">
                                                        @foreach ($dayPlans->take(5) as $plan)
                                                            <div class="flex items-center gap-1.5">
                                                                <div class="w-2 h-2 rounded-full {{ $colors[$plan->plan_type] ?? 'bg-gray-500' }}"></div>
                                                                <span class="truncate">{{ Str::limit($plan->title, 15) }}</span>
                                                            </div>
                                                        @endforeach
                                                        @if ($dayPlans->count() > 5)
                                                            <div class="text-gray-400 text-center">他{{ $dayPlans->count() - 5 }}件</div>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1.5 pt-1 border-t border-gray-600 text-center text-gray-300">
                                                        合計: {{ round($totalMinutes / 60, 1) }}時間
                                                    </div>
                                                    {{-- 三角形 --}}
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                            <div class="flex justify-center gap-4 sm:gap-6 mt-5 text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="w-3 h-3 bg-lask-accent-subtle rounded"></div>
                                    <span class="text-lask-text-secondary">学習</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-3 h-3 bg-[#6b8cae] rounded"></div>
                                    <span class="text-lask-text-secondary">作業</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-3 h-3 bg-[#8fbc8f] rounded"></div>
                                    <span class="text-lask-text-secondary">休憩</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 右側: サイドバー --}}
                <div class="space-y-6">
                    {{-- 未消化の課題 --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-lask-border">
                            <h3 class="text-lg font-semibold text-lask-text-primary flex items-center gap-2">
                                <x-icon name="clipboard-document-list" class="w-6 h-6 text-lask-1" />
                                未消化の課題
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-72 overflow-y-auto">
                            @forelse ($importedIssues->take(5) as $issue)
                                <div class="p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $issue->issue_key }}</span>
                                        @if ($issue->is_overdue)
                                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-lask-warning-light text-lask-warning">期限切れ</span>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-1">{{ $issue->summary }}</h4>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @if ($issue->due_date)
                                            <span>期限: {{ $issue->due_date->format('m/d') }}</span>
                                        @endif
                                        @if ($issue->estimated_hours)
                                            <span>{{ $issue->estimated_hours }}h</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    <p class="text-sm">課題がありません</p>
                                    <a href="{{ route('backlog.issues') }}" class="text-lask-1 text-sm hover:underline">
                                        インポート →
                                    </a>
                                </div>
                            @endforelse
                        </div>
                        @if ($importedIssues->count() > 5)
                            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 text-center">
                                <a href="{{ route('backlog.issues') }}" class="text-sm text-lask-1 hover:underline">
                                    すべて表示 ({{ $importedIssues->count() }}件) →
                                </a>
                            </div>
                        @endif
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
