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

            {{-- API成功メッセージ表示用 --}}
            <div id="api-success-message" class="hidden mb-6 p-4 bg-lask-success-light border border-lask-success text-lask-text-primary rounded-lg flex items-center gap-2 animate-pulse">
                <svg class="w-5 h-5 flex-shrink-0 text-lask-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="api-success-text"></span>
            </div>

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
                {{-- 左側: メインコンテンツエリア --}}
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
                            <button type="button" id="generate-plan-btn" class="px-6 py-3 bg-lask-accent text-white rounded-xl font-bold hover:bg-lask-accent-hover transition shadow-lg flex items-center gap-2">
                                <x-icon name="sparkles" class="w-5 h-5" />
                                計画を生成
                            </button>
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
                        <div class="px-6 py-4 border-b border-lask-border flex items-center justify-between">
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

                    {{-- 追加（岡部条） --}}
                    @php
                        // リクエストから年月を取得
                        $year = request('year', $year ?? now()->year);
                        $month = request('month', $month ?? now()->month);
                        $currentMonth = \Carbon\Carbon::create($year, $month, 1);
                        
                        $daysInMonth = $currentMonth->daysInMonth;
                        
                        // 前月・翌月のリンク用データ
                        $prevMonth = $currentMonth->copy()->subMonth();
                        $nextMonth = $currentMonth->copy()->addMonth();

                        // 色設定
                        $ganttColors = [
                            'study' => 'bg-lask-accent text-white',
                            'work' => 'bg-[#6b8cae] text-white',
                            'break' => 'bg-[#8fbc8f] text-white',
                            'review' => 'bg-[#2c3e50] text-gray-200',
                            'default' => 'bg-gray-400 text-white', // デフォルト色
                        ];
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-lask-border flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <h3 class="text-lg font-semibold text-lask-text-primary flex items-center gap-2">
                                    <x-icon name="calendar-days" class="w-6 h-6 text-lask-1" />
                                    {{ $currentMonth->year }}年{{ $currentMonth->month }}月の流れ
                                </h3>
                                
                                <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                                    <a href="{{ route('planning.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" 
                                    class="p-1 hover:bg-white dark:hover:bg-gray-600 rounded-md transition text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </a>
                                    <a href="{{ route('planning.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                                    class="p-1 hover:bg-white dark:hover:bg-gray-600 rounded-md transition text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            </div>

                            <a href="{{ route('planning.gantt', ['year' => $currentMonth->year, 'month' => $currentMonth->month]) }}" class="text-xs text-lask-1 hover:underline">
                                詳細を見る
                            </a>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <div class="min-w-[700px]">
                                    {{-- ヘッダー：日付 --}}
                                    <div class="grid border-b border-gray-100 dark:border-gray-700 mb-2" 
                                        style="grid-template-columns: 150px repeat({{ $daysInMonth }}, 1fr);">
                                        <div class="text-xs font-bold text-gray-500 py-2">タスク名</div>
                                        @for ($d = 1; $d <= $daysInMonth; $d++)
                                            @php $date = $currentMonth->copy()->addDays($d - 1); @endphp
                                            <div class="text-center pb-2">
                                                <div class="text-[9px] {{ $date->isWeekend() ? 'text-red-400' : 'text-gray-400' }}">{{ $date->isoFormat('dd') }}</div>
                                                <div class="text-[10px] font-medium {{ $date->isToday() ? 'text-lask-accent font-bold' : 'text-gray-600 dark:text-gray-300' }}">{{ $d }}</div>
                                            </div>
                                        @endfor
                                    </div>

                                    {{-- ボディ：タスクバーエリア --}}
                                    <div class="space-y-3 relative">
                                        {{-- 背景グリッド線 --}}
                                        <div class="absolute inset-0 grid h-full pointer-events-none" 
                                            style="grid-template-columns: 150px repeat({{ $daysInMonth }}, 1fr);">
                                            <div></div>
                                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                                <div class="border-r border-gray-50 dark:border-gray-800 h-full"></div>
                                            @endfor
                                        </div>

                                        {{-- ★修正: コントローラーから渡された $ganttTasks をループ --}}
                                        @forelse ($ganttTasks as $task)
                                            @php
                                                // 日付計算ロジック (DBの start_date/end_date を使用)
                                                $start = \Carbon\Carbon::parse($task->start_date);
                                                $end = \Carbon\Carbon::parse($task->end_date);
                                                
                                                // 表示範囲外ならスキップ
                                                if ($end->lt($currentMonth->copy()->startOfMonth()) || $start->gt($currentMonth->copy()->endOfMonth())) continue;

                                                // 今月の範囲内にクランプ（1日より前なら1日、月末より後なら月末）
                                                $startDay = $start->lt($currentMonth) ? 1 : $start->day;
                                                $endDay = $end->gt($currentMonth->copy()->endOfMonth()) ? $daysInMonth : $end->day;
                                                
                                                // バーの長さと位置を計算
                                                $duration = $endDay - $startDay + 1;
                                                $leftPos = (($startDay - 1) / $daysInMonth) * 100;
                                                $widthSize = ($duration / $daysInMonth) * 100;
                                                
                                                // DBにtypeカラムがない場合のデフォルト処理
                                                // (もしTaskテーブルにtypeがあれば $task->type を使用。
                                                $type = $task->type ?? 'work'; 
                                            @endphp

                                            <div class="grid items-center relative z-10 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition rounded"
                                                style="grid-template-columns: 150px 1fr;">
                                                
                                                <div class="pr-2 py-1 text-xs text-gray-700 dark:text-gray-200 truncate font-medium" title="{{ $task->title }}">
                                                    {{ $task->title }}
                                                </div>
                                                
                                                <div class="relative h-6 w-full">
                                                    <div class="absolute top-1/2 -translate-y-1/2 h-4 rounded text-[9px] flex items-center px-2 shadow-sm {{ $ganttColors[$type] ?? $ganttColors['default'] }}"
                                                        style="left: {{ $leftPos }}%; width: {{ $widthSize }}%; min-width: 10px;">
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-4 text-center text-xs text-gray-400 col-span-full">
                                                この月の予定はありません
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- //ここまで  岡部条（追加） --}}
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



    <script>
        // リロード後にsessionStorageからメッセージを表示
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = sessionStorage.getItem('planGenerateSuccess');
            if (successMessage) {
                const msgEl = document.getElementById('api-success-message');
                const textEl = document.getElementById('api-success-text');
                textEl.textContent = successMessage;
                msgEl.classList.remove('hidden');
                sessionStorage.removeItem('planGenerateSuccess');
                
                // 5秒後に自動で非表示
                setTimeout(() => {
                    msgEl.classList.add('hidden');
                }, 5000);
            }
        });

        document.getElementById('generate-plan-btn').addEventListener('click', async function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            // ボタンを無効化してローディング表示
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>生成中...';
            
            try {
                const response = await fetch('/api/planning/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // 成功メッセージをsessionStorageに保存してリロード
                    sessionStorage.setItem('planGenerateSuccess', data.message);
                    window.location.reload();
                } else {
                    // エラー時はアラート表示
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('API Error:', error);
                alert('計画の生成中にエラーが発生しました');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</x-app-layout>