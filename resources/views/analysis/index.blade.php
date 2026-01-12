<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('AIからの分析&アドバイス') }}
            </h2>
            <a href="{{ route('planning.index') }}" class="text-lask-1 hover:text-lask-1/80 font-medium">
                ← 計画に戻る
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- 概要統計 --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <x-icon name="clipboard-document-list" class="w-6 h-6 text-lask-1" />
                        <div class="text-4xl font-bold text-lask-text-primary">{{ $stats['total'] }}</div>
                    </div>
                    <div class="text-lask-text-secondary">全タスク</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <x-icon name="check-circle" class="w-6 h-6 text-lask-success" />
                        <div class="text-4xl font-bold text-lask-text-primary">{{ $stats['completion_rate'] }}%</div>
                    </div>
                    <div class="text-lask-text-secondary">完了率</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <x-icon name="arrow-path" class="w-6 h-6 text-lask-1" />
                        <div class="text-4xl font-bold text-lask-text-primary">{{ $stats['in_progress'] }}</div>
                    </div>
                    <div class="text-lask-text-secondary">進行中</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <x-icon name="exclamation-triangle" class="w-6 h-6 text-lask-warning" />
                        <div class="text-4xl font-bold text-lask-error">{{ $stats['failure_rate'] }}%</div>
                    </div>
                    <div class="text-lask-text-secondary">失敗率</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- 左側: 警告パターン --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-icon name="magnifying-glass" class="w-6 h-6 text-lask-1" />
                                検出されたパターン
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse ($patterns as $pattern)
                                <x-warning-card :pattern="$pattern" />
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <x-icon name="sparkles" class="w-10 h-10 mx-auto mb-2 text-lask-1" />
                                    <p>問題のあるパターンは検出されませんでした</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- 週間グラフ --}}
                    @php
                        // コントローラーから渡される $weeklyData を使用（動的データ）
                        $chartData = $weeklyData;
                        $dailyGoal = 5; // 1日の目標値
                        $maxBarValue = 8; // グラフの最大値
                        $todayDayOfWeek = now()->dayOfWeek ?: 7; // 0=日曜 → 7に変換
                        if ($todayDayOfWeek == 0) $todayDayOfWeek = 7;
                    @endphp


                    <style>
                        /* 棒グラフのアニメーション */
                        @keyframes growUp {
                            from { transform: scaleY(0); }
                            to { transform: scaleY(1); }
                        }
                        .bar-animate {
                            transform-origin: bottom;
                            animation: growUp 0.6s ease-out forwards;
                        }
                        .bar-animate-delay-1 { animation-delay: 0.1s; }
                        .bar-animate-delay-2 { animation-delay: 0.2s; }
                        .bar-animate-delay-3 { animation-delay: 0.3s; }
                        .bar-animate-delay-4 { animation-delay: 0.4s; }
                        .bar-animate-delay-5 { animation-delay: 0.5s; }
                        .bar-animate-delay-6 { animation-delay: 0.6s; }
                        .bar-animate-delay-7 { animation-delay: 0.7s; }

                        /* 斜線パターン */
                        .pattern-completed {
                            background: repeating-linear-gradient(
                                45deg,
                                transparent,
                                transparent 3px,
                                rgba(255,255,255,0.15) 3px,
                                rgba(255,255,255,0.15) 6px
                            );
                        }
                        .pattern-failed {
                            background: repeating-linear-gradient(
                                -45deg,
                                transparent,
                                transparent 3px,
                                rgba(255,255,255,0.15) 3px,
                                rgba(255,255,255,0.15) 6px
                            );
                        }

                        /* ツールチップ */
                        .chart-tooltip {
                            visibility: hidden;
                            opacity: 0;
                            transform: translateY(5px);
                            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
                            pointer-events: none;
                            background-color: rgba(17, 24, 39, 0.95) !important;
                        }
                        .chart-bar-group:hover .chart-tooltip {
                            visibility: visible;
                            opacity: 1;
                            transform: translateY(0);
                        }
                        /* ダークモードのツールチップ背景 */
                        .dark .chart-tooltip {
                            background-color: rgba(55, 65, 81, 0.98) !important;
                        }
                    </style>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-icon name="chart-bar" class="w-6 h-6 text-lask-1" />
                                今週の進捗
                            </h3>
                        </div>
                        <div class="p-6">
                            {{-- グラフエリア --}}
                            <div class="relative">

                                {{-- 棒グラフ --}}
                                <div class="flex items-end justify-between gap-2 sm:gap-3 pt-10 min-h-[180px] sm:min-h-[200px] lg:min-h-[220px]">
                                    @foreach ($chartData as $index => $day)
                                        @php
                                            $total = $day['completed'] + $day['failed'];
                                            $completedHeight = ($day['completed'] / $maxBarValue) * 100;
                                            $failedHeight = ($day['failed'] / $maxBarValue) * 100;
                                            // Carbonで今日判定（フォーマット差異も吸収）
                                            $isToday = (\Carbon\Carbon::parse($day['date'])->isSameDay(now()));
                                            $hasData = $total > 0;
                                        @endphp
                                        
                                        <div class="flex-1 flex flex-col items-center chart-bar-group relative">
                                            {{-- ツールチップ --}}
                                            @if ($hasData)
                                                <div class="chart-tooltip absolute -top-20 left-1/2 -translate-x-1/2 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap z-[100] border border-gray-600" style="background-color: rgba(17, 24, 39, 0.95);">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="font-medium">完了: {{ $day['completed'] }}件</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3 h-3 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="font-medium">失敗: {{ $day['failed'] }}件</span>
                                                    </div>
                                                    {{-- 三角形の吹き出し --}}
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent" style="border-top-color: rgba(17, 24, 39, 0.95);"></div>
                                                </div>
                                            @endif

                                            {{-- 合計値表示（バー最上部） --}}
                                            @if ($hasData)
                                                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 px-2 py-1 rounded-md shadow-md border border-gray-300 dark:border-gray-500 z-[90]">
                                                    {{ $total }}
                                                </div>
                                            @endif

                                            <div 
                                                class="w-full flex flex-col-reverse rounded-lg overflow-hidden {{ $isToday ? 'ring-2 ring-lask-accent ring-offset-2 dark:ring-offset-gray-800' : '' }}"
                                                style="height: 120px; {{ $isToday ? 'background: var(--color-accent-subtle);' : '' }}"
                                            >
                                                @if ($hasData)
                                                    {{-- 完了バー（下に表示）- 緑色 --}}
                                                    @if ($day['completed'] > 0)
                                                        <div 
                                                            class="w-full flex items-center justify-center"
                                                            style="height: {{ $completedHeight }}%; background-color: #d3f3d9; background-image: repeating-linear-gradient(45deg, transparent, transparent 3px, rgba(0,0,0,0.1) 3px, rgba(0,0,0,0.1) 6px);"
                                                        >
                                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20" style="opacity: 0.8;">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    {{-- 失敗バー（上に表示）- 赤色 --}}
                                                    @if ($day['failed'] > 0)
                                                        <div 
                                                            class="w-full flex items-center justify-center"
                                                            style="height: {{ $failedHeight }}%; background-color: #e47d7c; background-image: repeating-linear-gradient(-45deg, transparent, transparent 3px, rgba(0,0,0,0.1) 3px, rgba(0,0,0,0.1) 6px);"
                                                        >
                                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20" style="opacity: 0.8;">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                @else
                                                    {{-- データなしのプレースホルダー --}}
                                                    <div class="w-full h-full rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700/30">
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">—</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mt-2 text-center">
                                                <div class="text-xs font-medium {{ $isToday ? 'text-lask-1' : 'text-gray-600 dark:text-gray-400' }}">
                                                    {{ $day['day'] }}
                                                    @if ($isToday)
                                                        <span class="ml-1 text-[10px] bg-lask-accent-subtle text-lask-1 px-1 rounded">今日</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                                    {{ \Carbon\Carbon::parse($day['date'])->format('n/j') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- 凡例 --}}
                            <div class="flex justify-center gap-6 mt-6 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-lask-success rounded flex items-center justify-center">
                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-400">完了</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-lask-warning rounded flex items-center justify-center">
                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-400">失敗</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- カテゴリ別統計 --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <x-icon name="folder" class="w-6 h-6 mr-2 text-lask-1" />
                                カテゴリ別完了率
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach ($stats['by_category'] as $category => $data)
                                @php
                                    $icons = ['study' => 'book-open', 'work' => 'briefcase', 'personal' => 'home'];
                                    $iconColors = ['study' => 'text-lask-1', 'work' => 'text-lask-1', 'personal' => 'text-lask-3'];
                                    $labels = ['study' => '勉強', 'work' => '仕事', 'personal' => '個人'];
                                @endphp
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <x-icon :name="$icons[$category] ?? 'clipboard-document-list'" class="w-4 h-4 {{ $iconColors[$category] ?? 'text-gray-500' }}" />
                                            {{ $labels[$category] ?? $category }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            @if ($data['total'] == 0)
                                                データなし
                                            @else
                                                {{ $data['completed'] }}/{{ $data['total'] }} ({{ $data['rate'] }}%)
                                            @endif
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        @if ($data['total'] == 0)
                                            {{-- データなしの場合はグレーで全体を表示 --}}
                                            <div class="h-3 rounded-full bg-gray-400 dark:bg-gray-500" style="width: 100%"></div>
                                        @else
                                            <div 
                                                class="h-3 rounded-full transition-all duration-500 {{ $data['rate'] >= 70 ? 'bg-lask-success' : ($data['rate'] >= 40 ? 'bg-lask-4' : 'bg-lask-warning') }}"
                                                style="width: {{ $data['rate'] }}%"
                                            ></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 右側: アドバイス --}}
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden" x-data="{
                        analysisStarted: false,
                        analyzing: false,
                        apiAdvice: [],
                        error: null,
                        async startAnalysis() {
                            this.analyzing = true;
                            this.error = null;
                            try {
                                const response = await fetch('/api/analysis/advice', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content || '',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ refresh: true })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    this.apiAdvice = data.data.advice;
                                    this.analysisStarted = true;
                                    Alpine.store('notifications')?.showToast('分析が完了しました', 'success');
                                } else {
                                    this.error = 'アドバイスの取得に失敗しました';
                                    Alpine.store('notifications')?.showToast('アドバイスの取得に失敗しました', 'error');
                                }
                            } catch (e) {
                                this.error = 'APIエラーが発生しました';
                                Alpine.store('notifications')?.showToast('APIエラーが発生しました', 'error');
                                console.error(e);
                            } finally {
                                this.analyzing = false;
                            }
                        },
                        getTagClass(type) {
                            if (type === 'warning') return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                            if (type === 'recommend') return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
                            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                        },
                        getIcon(type) {
                            if (type === 'warning') return 'exclamation-triangle';
                            if (type === 'recommend') return 'light-bulb';
                            return 'information-circle';
                        }
                    }">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-icon name="light-bulb" class="w-6 h-6 text-lask-1" />
                                AIからのアドバイス
                            </h3>
                            <button 
                                @click="startAnalysis()" 
                                :disabled="analyzing"
                                class="px-4 py-2 bg-lask-accent text-white text-sm font-medium rounded-lg hover:opacity-80 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <template x-if="!analyzing">
                                    <span class="flex items-center gap-2">
                                        <x-icon name="sparkles" class="w-4 h-4" />
                                        <span x-text="analysisStarted ? '再分析' : '分析を開始'"></span>
                                    </span>
                                </template>
                                <template x-if="analyzing">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        分析中...
                                    </span>
                                </template>
                            </button>
                        </div>
                        <div class="p-4 space-y-3">
                            {{-- 分析開始前のみ表示（サーバー側のアドバイス） --}}
                            <div x-show="!analysisStarted" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                @forelse ($advice as $item)
                                    <div class="mb-3">
                                        <x-advice-card :advice="$item" />
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                        <x-icon name="cpu-chip" class="w-8 h-8 mx-auto mb-2 text-gray-400" />
                                        <p class="text-sm">「分析を開始」ボタンをクリックしてAIアドバイスを取得してください</p>
                                    </div>
                                @endforelse
                            </div>
                            
                            {{-- エラー表示 --}}
                            <div x-show="error" x-cloak class="text-center py-6">
                                <div class="text-red-500 dark:text-red-400">
                                    <x-icon name="exclamation-circle" class="w-8 h-8 mx-auto mb-2" />
                                    <p x-text="error"></p>
                                </div>
                            </div>
                            
                            {{-- API分析結果 --}}
                            <div 
                                x-show="analysisStarted && !error"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="space-y-3"
                            >
                                <template x-for="(item, index) in apiAdvice" :key="index">
                                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0">
                                                <div :class="getTagClass(item.type)" class="w-8 h-8 rounded-lg flex items-center justify-center">
                                                    <template x-if="item.type === 'warning'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                    </template>
                                                    <template x-if="item.type === 'recommend'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                        </svg>
                                                    </template>
                                                    <template x-if="item.type === 'info'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100" x-text="item.title"></h4>
                                                    <span :class="getTagClass(item.type)" class="px-2 py-0.5 text-xs font-medium rounded-full" x-text="item.tag"></span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="item.description"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- クイックアクション --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-lask-accent/30">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <x-icon name="rocket-launch" class="w-5 h-5 text-lask-1" />
                            次のアクション
                        </h3>
                        <div class="space-y-3">
                            <a href="{{ route('backlog.issues') }}" class="flex items-center gap-3 p-3 bg-lask-accent-subtle rounded-xl hover:bg-lask-accent/20 transition text-gray-700 dark:text-gray-300">
                                <x-icon name="inbox-arrow-down" class="w-5 h-5 text-lask-1" />
                                <span>課題をインポート</span>
                            </a>
                            <a href="{{ route('planning.index') }}" class="flex items-center gap-3 p-3 bg-lask-accent-subtle rounded-xl hover:bg-lask-accent/20 transition text-gray-700 dark:text-gray-300">
                                <x-icon name="calendar" class="w-5 h-5 text-lask-1" />
                                <span>計画ダッシュボード</span>
                            </a>
                            <a href="{{ route('planning.calendar') }}" class="flex items-center gap-3 p-3 bg-lask-accent-subtle rounded-xl hover:bg-lask-accent/20 transition text-gray-700 dark:text-gray-300">
                                <x-icon name="calendar" class="w-5 h-5 text-lask-1" />
                                <span>カレンダーを見る</span>
                            </a>
                            <a href="{{ route('planning.gantt') }}" class="flex items-center gap-3 p-3 bg-lask-accent-subtle rounded-xl hover:bg-lask-accent/20 transition text-gray-700 dark:text-gray-300">
                                <x-icon name="bars-3-bottom-left" class="w-5 h-5 text-lask-1" />
                                <span>ガントチャートを見る</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
