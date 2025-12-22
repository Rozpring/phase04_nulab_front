<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- メイングリッド: 3:5:4 の配分 --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {{-- 左側: 進捗ドーナツチャート（3/12幅） --}}
                <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 self-start">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <x-icon name="chart-pie" class="w-5 h-5 text-lask-1" />
                        今日の進捗
                    </h3>
                    
                    <div class="flex flex-col items-center" x-data="{ 
                        activePopup: null,
                        tasks: {
                            not_started: {{ Js::from($tasksByStatus['not_started']->map(fn($p) => ['title' => $p->title, 'time' => $p->scheduled_time?->format('H:i')])) }},
                            in_progress: {{ Js::from($tasksByStatus['in_progress']->map(fn($p) => ['title' => $p->title, 'time' => $p->scheduled_time?->format('H:i')])) }},
                            processed: {{ Js::from($tasksByStatus['processed']->map(fn($p) => ['title' => $p->title, 'time' => $p->scheduled_time?->format('H:i')])) }},
                            completed: {{ Js::from($tasksByStatus['completed']->map(fn($p) => ['title' => $p->title, 'time' => $p->scheduled_time?->format('H:i')])) }}
                        },
                        labels: {
                            not_started: '未対応',
                            in_progress: '処理中',
                            processed: '処理済み',
                            completed: '完了'
                        },
                        colors: {
                            not_started: '#9ca3af',
                            in_progress: '#3b82f6',
                            processed: '#f59e0b',
                            completed: '#10b981'
                        },
                        togglePopup(status) {
                            this.activePopup = this.activePopup === status ? null : status;
                        }
                    }">
                        {{-- ドーナツチャート --}}
                        <div class="relative w-40 h-40 mb-4">
                            @php
                                $total = $progress['total'];
                                $hasData = $total > 0;
                                // 意味のある配色：グレー=未対応、青=処理中、黄=処理済み、緑=完了
                                $segments = [
                                    ['key' => 'not_started', 'label' => '未対応', 'value' => $progress['not_started'], 'color' => '#9ca3af'],
                                    ['key' => 'in_progress', 'label' => '処理中', 'value' => $progress['in_progress'], 'color' => '#3b82f6'],
                                    ['key' => 'processed', 'label' => '処理済み', 'value' => $progress['processed'], 'color' => '#f59e0b'],
                                    ['key' => 'completed', 'label' => '完了', 'value' => $progress['completed'], 'color' => '#10b981'],
                                ];
                                $currentAngle = -90;
                                $radius = 70;
                                $innerRadius = 45;
                            @endphp
                            
                            <svg viewBox="0 0 200 200" class="w-full h-full">
                                @if (!$hasData)
                                    {{-- データがない場合 --}}
                                    <circle cx="100" cy="100" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="{{ $radius - $innerRadius }}" class="dark:stroke-gray-600"/>
                                    <text x="100" y="95" text-anchor="middle" fill="#9ca3af" font-size="12" class="dark:fill-gray-400">計画なし</text>
                                    <text x="100" y="110" text-anchor="middle" fill="#6b7280" font-size="10" class="dark:fill-gray-500">0/0</text>
                                @else
                                    {{-- ドーナツセグメント --}}
                                    @foreach ($segments as $segment)
                                        @php
                                            if ($segment['value'] <= 0) continue;
                                            
                                            $percentage = $segment['value'] / $total;
                                            $angle = $percentage * 360;
                                            
                                            // 円弧の中心半径
                                            $centerRadius = ($radius + $innerRadius) / 2;
                                            $strokeWidth = $radius - $innerRadius;
                                            
                                            // 円周の計算
                                            $circumference = 2 * M_PI * $centerRadius;
                                            $dashLength = ($angle / 360) * $circumference;
                                            $dashGap = $circumference - $dashLength;
                                            
                                            // 開始位置（回転オフセット）
                                            $rotateOffset = $currentAngle + 90;
                                            
                                            $currentAngle += $angle;
                                        @endphp
                                        <circle 
                                            cx="100" cy="100" 
                                            r="{{ $centerRadius }}"
                                            fill="none"
                                            stroke="{{ $segment['color'] }}"
                                            stroke-width="{{ $strokeWidth }}"
                                            stroke-dasharray="{{ $dashLength }} {{ $dashGap }}"
                                            transform="rotate({{ $rotateOffset }} 100 100)"
                                            class="cursor-pointer hover:opacity-80 transition-opacity"
                                            @click="togglePopup('{{ $segment['key'] }}')"
                                        />
                                    @endforeach
                                    
                                    {{-- 中央のテキスト --}}
                                    <text x="100" y="92" text-anchor="middle" fill="#10b981" font-size="28" font-weight="700" class="dark:fill-emerald-400">{{ $progress['completed'] }}</text>
                                    <text x="100" y="108" text-anchor="middle" fill="#6b7280" font-size="11" class="dark:fill-gray-400">/{{ $total }} 完了</text>
                                @endif
                            </svg>
                        </div>

                        {{-- 凡例 --}}
                        <div class="grid grid-cols-2 gap-1 text-xs w-full">
                            <button @click="togglePopup('not_started')" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-left" :class="{ 'ring-1 ring-gray-400': activePopup === 'not_started' }">
                                <div class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                                <span class="text-gray-600 dark:text-gray-400">未対応 ({{ $progress['not_started'] }})</span>
                            </button>
                            <button @click="togglePopup('in_progress')" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-left" :class="{ 'ring-1 ring-blue-400': activePopup === 'in_progress' }">
                                <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                <span class="text-gray-600 dark:text-gray-400">処理中 ({{ $progress['in_progress'] }})</span>
                            </button>
                            <button @click="togglePopup('processed')" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-left" :class="{ 'ring-1 ring-amber-400': activePopup === 'processed' }">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                <span class="text-gray-600 dark:text-gray-400">処理済み ({{ $progress['processed'] }})</span>
                            </button>
                            <button @click="togglePopup('completed')" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition text-left" :class="{ 'ring-1 ring-emerald-400': activePopup === 'completed' }">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                <span class="text-gray-600 dark:text-gray-400">完了 ({{ $progress['completed'] }})</span>
                            </button>
                        </div>

                        {{-- 完了率プログレスバー --}}
                        <div class="w-full mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">完了率</span>
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $progress['completion_rate'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-500 ease-out"
                                    style="width: {{ $progress['completion_rate'] }}%"
                                ></div>
                            </div>
                            @if ($progress['total'] > 0)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 text-center">
                                    {{ $progress['completed'] }} / {{ $progress['total'] }} タスク完了
                                </p>
                            @endif
                        </div>

                        {{-- ポップアップ --}}
                        <div 
                            x-show="activePopup !== null"
                            x-transition
                            class="w-full mt-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                            @click.away="activePopup = null"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100" x-text="labels[activePopup] + ' のタスク'"></h4>
                                <button @click="activePopup = null" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="max-h-32 overflow-y-auto">
                                <template x-if="activePopup && tasks[activePopup].length === 0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">タスクなし</p>
                                </template>
                                <template x-for="(task, index) in (activePopup ? tasks[activePopup] : [])" :key="index">
                                    <div class="flex items-center gap-2 py-1 text-xs border-b border-gray-200 dark:border-gray-600 last:border-0">
                                        <span class="text-gray-400 w-10" x-text="task.time || '--:--'"></span>
                                        <span class="text-gray-700 dark:text-gray-300" x-text="task.title"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 中央: 今日の計画（5/12幅） --}}
                <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-icon name="calendar" class="w-5 h-5 text-lask-1" />
                            今日の計画
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ today()->isoFormat('M月D日 (ddd)') }} / {{ $todayPlans->count() }}件</p>
                    </div>
                    
                    @if ($todayPlans->isEmpty())
                        <div class="p-6 text-center">
                            <x-icon name="document-text" class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">計画がありません</p>
                            <a href="{{ route('planning.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-lask-accent text-white text-sm rounded-lg hover:bg-lask-accent-hover transition">
                                <x-icon name="plus" class="w-4 h-4" />
                                計画を生成
                            </a>
                        </div>
                    @else
                        @php
                            // 完了・スキップタスクを下に表示するためにソート
                            $sortedPlans = $todayPlans->sortBy(function($plan) {
                                // 未完了タスクを上に（0-1）、完了系を下に（2-3）
                                return match($plan->status) {
                                    'in_progress' => 0,  // 進行中が最上位
                                    'planned' => 1,      // 予定が次
                                    'completed' => 2,    // 完了は下
                                    'skipped' => 3,      // スキップは最下位
                                    default => 1,
                                };
                            });
                        @endphp
                        <div class="space-y-3 p-3">
                            @foreach ($sortedPlans->take(6) as $plan)
                                <x-plan-block :plan="$plan" />
                            @endforeach
                        </div>
                        @if ($todayPlans->count() > 6)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/30 text-center">
                                <a href="{{ route('planning.timeline') }}" class="text-lask-1 hover:underline text-sm">
                                    他{{ $todayPlans->count() - 6 }}件を表示 →
                                </a>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- 右側: 期限間近 + 活動ログ（4/12幅） --}}
                <div class="lg:col-span-4 space-y-6">
                    {{-- 期限間近のタスク --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-icon name="exclamation-triangle" class="w-5 h-5 text-lask-warning" />
                                期限間近
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-48 overflow-y-auto">
                            @forelse ($upcomingDeadlines as $issue)
                                <div class="px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $issue->issue_key }}</span>
                                        @php
                                            $hoursLeft = (int) now()->diffInHours($issue->due_date, false);
                                            $daysLeft = (int) floor($hoursLeft / 24);
                                            $remainingHours = $hoursLeft % 24;
                                            
                                            // 緊急度クラス
                                            $urgencyClass = $daysLeft < 1 ? 'bg-lask-error-light text-lask-error' 
                                                : ($daysLeft <= 3 ? 'bg-lask-warning-light text-lask-warning'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300');
                                            
                                            // 表示テキスト
                                            if ($hoursLeft < 1) {
                                                $timeText = 'まもなく';
                                            } elseif ($hoursLeft < 24) {
                                                $timeText = '約' . $hoursLeft . '時間後';
                                            } elseif ($daysLeft == 0) {
                                                $timeText = '今日';
                                            } elseif ($daysLeft == 1) {
                                                $timeText = '明日';
                                            } else {
                                                $timeText = '約' . $daysLeft . '日後';
                                            }
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $urgencyClass }}">
                                            {{ $timeText }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1.5 line-clamp-1">{{ $issue->summary }}</p>
                                </div>
                            @empty
                                <div class="p-4 text-center">
                                    <x-icon name="check-circle" class="w-8 h-8 mx-auto mb-2 text-lask-1" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400">期限間近のタスクなし</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- 最近の活動 --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-icon name="clock" class="w-5 h-5 text-lask-1" />
                                最近の活動
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-48 overflow-y-auto">
                            @forelse ($recentActivity as $activity)
                                <div class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if ($activity->status === 'completed')
                                            <x-icon name="check-circle" class="w-4 h-4 text-lask-1" />
                                        @else
                                            <x-icon name="x-circle" class="w-4 h-4 text-gray-400" />
                                        @endif
                                        <span class="text-sm text-gray-700 dark:text-gray-300 line-clamp-1">{{ $activity->title }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 ml-6">
                                        {{ $activity->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            @empty
                                <div class="p-4 text-center">
                                    <x-icon name="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400">最近の活動なし</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

