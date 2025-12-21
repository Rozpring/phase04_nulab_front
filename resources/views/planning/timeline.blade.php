<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('planning.timeline', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" 
                    class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <span class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $date->format('Y年m月d日') }}</span>
                <a href="{{ route('planning.timeline', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @if (!$date->isToday())
                    <a href="{{ route('planning.timeline') }}" class="text-sm text-lask-1 hover:underline px-2 py-1 bg-lask-accent-subtle rounded">
                        今日に戻る
                    </a>
                @endif
            </div>
            {{-- ビュー切替タブ --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <a href="{{ route('planning.index') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.index') ? 'bg-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-icon name="clipboard-document-list" class="w-4 h-4" />
                    カンバン
                </a>
                <a href="{{ route('planning.timeline') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.timeline') ? 'bg-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-icon name="clock" class="w-4 h-4" />
                    タイムライン
                </a>
                <a href="{{ route('planning.calendar') }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition flex items-center gap-1 {{ request()->routeIs('planning.calendar') ? 'bg-white dark:bg-gray-800 text-lask-1 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-icon name="calendar" class="w-4 h-4" />
                    カレンダー
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- メインタイムライン --}}
                <div class="lg:col-span-2">
                    <div 
                        x-data="{
                            currentHour: new Date().getHours(),
                            currentMinute: new Date().getMinutes(),
                            isToday: {{ $date->isToday() ? 'true' : 'false' }},
                            plans: {{ Js::from($plans->map(fn($p) => [
                                'id' => $p->id,
                                'title' => $p->title,
                                'scheduled_time' => $p->scheduled_time?->format('H:i'),
                                'end_time' => $p->end_time?->format('H:i'),
                                'plan_type' => $p->plan_type,
                                'status' => $p->status,
                                'duration_minutes' => $p->duration_minutes
                            ])) }},
                            
                            init() {
                                if (this.isToday) {
                                    setInterval(() => {
                                        this.currentHour = new Date().getHours();
                                        this.currentMinute = new Date().getMinutes();
                                    }, 1000);
                                }
                            },
                            
                            getCurrentPlan() {
                                const now = this.currentHour * 60 + this.currentMinute;
                                return this.plans.find(p => {
                                    if (!p.scheduled_time || !p.end_time) return false;
                                    const [sh, sm] = p.scheduled_time.split(':').map(Number);
                                    const [eh, em] = p.end_time.split(':').map(Number);
                                    const start = sh * 60 + sm;
                                    const end = eh * 60 + em;
                                    return now >= start && now < end;
                                });
                            },
                            
                            getRemainingTime(plan) {
                                if (!plan || !plan.end_time) return '';
                                const [eh, em] = plan.end_time.split(':').map(Number);
                                const end = eh * 60 + em;
                                const now = this.currentHour * 60 + this.currentMinute;
                                const remaining = end - now;
                                if (remaining <= 0) return '終了';
                                const h = Math.floor(remaining / 60);
                                const m = remaining % 60;
                                return h > 0 ? `残り ${h}時間${m}分` : `残り ${m}分`;
                            },
                            
                            get markerPosition() {
                                return (this.currentMinute / 60) * 100;
                            }
                        }"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm"
                    >
                        {{-- ヘッダー --}}
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @if ($date->isToday())
                                    <x-icon name="sun" class="w-8 h-8 text-amber-500" />
                                @else
                                    <x-icon name="calendar" class="w-8 h-8 text-gray-400" />
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $date->isoFormat('M月D日 (ddd)') }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $plans->count() }}件の計画 / 
                                        {{ round($plans->sum('duration_minutes') / 60, 1) }}時間
                                    </p>
                                </div>
                            </div>
                            {{-- リアルタイム時計 --}}
                            @if($date->isToday())
                            <div class="text-right">
                                <div class="text-2xl font-mono font-bold text-lask-1" 
                                    x-text="String(currentHour).padStart(2, '0') + ':' + String(currentMinute).padStart(2, '0')">
                                </div>
                                <template x-if="getCurrentPlan()">
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="getRemainingTime(getCurrentPlan())"></div>
                                </template>
                            </div>
                            @endif
                        </div>

                        {{-- タイムライン --}}
                        <div class="p-6">
                            <div class="relative">
                                {{-- 時間軸 --}}
                                @foreach ($timeSlots as $slot)
                                    <div class="flex min-h-[60px] border-t border-gray-100 dark:border-gray-700">
                                        {{-- 時刻ラベル --}}
                                        <div class="w-16 flex-shrink-0 text-right pr-4 pt-1">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $slot['label'] }}</span>
                                        </div>
                                        
                                        {{-- コンテンツエリア --}}
                                        <div class="flex-1 pl-4 border-l-2 border-gray-200 dark:border-gray-600 relative">
                                            @foreach ($slot['plans'] as $plan)
                                                <x-plan-card-interactive :plan="$plan" />
                                            @endforeach

                                            {{-- 現在時刻マーカー --}}
                                            @if ($date->isToday())
                                            <template x-if="currentHour === {{ $slot['hour'] }}">
                                                <div 
                                                    class="absolute left-0 right-0 h-0.5 bg-rose-500 z-10 -ml-[5px] pointer-events-none transition-all duration-1000"
                                                    :style="`top: ${markerPosition}%`"
                                                >
                                                    <div class="absolute -left-1 -top-1 w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                                                    <div class="absolute left-4 -top-2.5 px-2 py-0.5 bg-rose-500 text-white text-xs rounded font-medium">
                                                        <span x-text="String(currentHour).padStart(2, '0') + ':' + String(currentMinute).padStart(2, '0')"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- フッター --}}
                        @if ($plans->isEmpty())
                            <div class="px-6 py-8 text-center border-t border-gray-200 dark:border-gray-700">
                                <x-icon name="document-text" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
                                <p class="text-gray-500 dark:text-gray-400 mb-4">この日の計画はありません</p>
                                <button 
                                    @click="$store.ui.openModal('planCreate')"
                                    class="px-4 py-2 bg-lask-accent text-white rounded-lg hover:bg-lask-accent-hover transition flex items-center gap-2 mx-auto"
                                >
                                    <x-icon name="plus" class="w-4 h-4" />
                                    計画を追加
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- サイドバー --}}
                <div class="space-y-6">
                    {{-- ポモドーロタイマー --}}
                    <x-pomodoro-timer />

                    {{-- 凡例 --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">凡例</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-lask-accent rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">学習</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-lask-1 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">作業</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-lask-4 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">休憩</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-lask-3 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">復習</span>
                            </div>
                        </div>
                    </div>

                    {{-- クイックリンク --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">クイックリンク</h4>
                        <div class="space-y-2">
                            <a href="{{ route('planning.index') }}" class="flex items-center gap-2 p-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <x-icon name="chart-bar" class="w-4 h-4 text-lask-1" />
                                計画ダッシュボード
                            </a>
                            <a href="{{ route('planning.calendar') }}" class="flex items-center gap-2 p-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <x-icon name="calendar" class="w-4 h-4 text-lask-1" />
                                カレンダー
                            </a>
                            <a href="{{ route('analysis.index') }}" class="flex items-center gap-2 p-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                <x-icon name="cpu-chip" class="w-4 h-4 text-lask-1" />
                                AI分析
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

