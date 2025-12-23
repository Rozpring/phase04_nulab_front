<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('planning.calendar', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}" 
                    class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <span class="font-semibold text-xl text-gray-800 dark:text-gray-200 min-w-[120px] text-center">
                    {{ $year }}年{{ $month }}月
                </span>
                <a href="{{ route('planning.calendar', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @if ($year != now()->year || $month != now()->month)
                    <a href="{{ route('planning.calendar') }}" class="text-sm text-lask-1 hover:underline px-2 py-1 bg-lask-accent-subtle rounded">
                        今月
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
        <div class="w-full sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                {{-- 曜日ヘッダー --}}
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                    @foreach (['日', '月', '火', '水', '木', '金', '土'] as $i => $dayName)
                        <div class="px-3 py-2 text-center text-sm font-medium 
                            {{ $i == 0 ? 'text-lask-warning' : ($i == 6 ? 'text-lask-1' : 'text-gray-500 dark:text-gray-400') }}">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                {{-- カレンダー本体 --}}
                @foreach ($calendar as $week)
                    <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        @foreach ($week as $i => $day)
                            @php
                                $isWeekend = $i == 0 || $i == 6;
                            @endphp
                            <div class="min-h-[100px] p-2 border-r border-gray-200 dark:border-gray-700 last:border-r-0 relative group
                                {{ !$day['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-900/50' : '' }}">
                                {{-- 日付 --}}
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium 
                                        {{ !$day['isCurrentMonth'] ? 'text-gray-400 dark:text-gray-600' : '' }}
                                        {{ $day['isToday'] ? 'w-6 h-6 bg-lask-accent text-white rounded-full flex items-center justify-center' : '' }}
                                        {{ $isWeekend && $day['isCurrentMonth'] && !$day['isToday'] ? ($i == 0 ? 'text-lask-warning' : 'text-lask-1') : (!$day['isToday'] ? 'text-gray-900 dark:text-gray-100' : '') }}">
                                        {{ $day['day'] }}
                                    </span>
                                    @if ($day['plans']->count() > 0)
                                        <a href="{{ route('planning.timeline', ['date' => $day['date']->format('Y-m-d')]) }}" 
                                            class="text-xs text-lask-1 hover:underline">
                                            詳細
                                        </a>
                                    @endif
                                </div>

                                {{-- 計画 --}}
                                <div class="space-y-1">
                                    @foreach ($day['plans']->take(3) as $plan)
                                        @php
                                            $colors = [
                                                'study' => 'bg-lask-accent-subtle text-lask-text-primary',
                                                'work' => 'bg-[#6b8cae]/40 text-lask-text-primary',
                                                'break' => 'bg-[#8fbc8f]/50 text-lask-text-primary',
                                                'review' => 'bg-[#2c3e50]/30 text-lask-text-primary',
                                            ];
                                            $dotColors = [
                                                'study' => 'bg-lask-accent',
                                                'work' => 'bg-lask-1',
                                                'break' => 'bg-lask-4',
                                                'review' => 'bg-lask-3',
                                            ];
                                        @endphp
                                        <div class="text-xs px-1.5 py-0.5 rounded {{ $colors[$plan->plan_type] ?? 'bg-gray-100 text-gray-800' }} truncate">
                                            {{ Str::limit($plan->title, 12) }}
                                        </div>
                                    @endforeach
                                    @if ($day['plans']->count() > 3)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                            +{{ $day['plans']->count() - 3 }}件
                                        </div>
                                    @endif
                                </div>

                                {{-- ホバーツールチップ --}}
                                @if ($day['plans']->count() > 0 && $day['isCurrentMonth'])
                                    {{-- 左端(日曜)は左寄せ、右端(土曜)は右寄せ、それ以外は中央 --}}
                                    <div class="absolute top-full mt-1 hidden group-hover:block z-[9999]
                                        {{ $i == 0 ? 'left-0' : ($i == 6 ? 'right-0' : 'left-1/2 -translate-x-1/2') }}">
                                        <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg px-3 py-2 shadow-xl border border-gray-600" style="min-width: 160px; max-width: 200px;">
                                            <div class="font-semibold mb-1.5 text-center border-b border-gray-600 pb-1">
                                                {{ $day['date']->isoFormat('M月D日 (ddd)') }}
                                            </div>
                                            <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                                @foreach ($day['plans']->take(6) as $plan)
                                                    <div class="flex items-start gap-1.5">
                                                        <div class="w-2 h-2 rounded-full mt-1 flex-shrink-0 {{ $dotColors[$plan->plan_type] ?? 'bg-gray-500' }}"></div>
                                                        <div>
                                                            <div class="font-medium">{{ Str::limit($plan->title, 20) }}</div>
                                                            @if ($plan->scheduled_time)
                                                                <div class="text-gray-400 text-[10px]">{{ $plan->scheduled_time->format('H:i') }} ({{ $plan->duration_minutes }}分)</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if ($day['plans']->count() > 6)
                                                    <div class="text-gray-400 text-center pt-1">他{{ $day['plans']->count() - 6 }}件</div>
                                                @endif
                                            </div>
                                            <div class="mt-1.5 pt-1 border-t border-gray-600 text-center text-gray-300">
                                                {{ $day['plans']->count() }}件 / {{ round($day['plans']->sum('duration_minutes') / 60, 1) }}時間
                                            </div>
                                            {{-- 三角形（位置も曜日に応じて調整） --}}
                                            <div class="absolute bottom-full border-4 border-transparent border-b-gray-900 dark:border-b-gray-700
                                                {{ $i == 0 ? 'left-4' : ($i == 6 ? 'right-4' : 'left-1/2 -translate-x-1/2') }}"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- フッター --}}
        <div class="mt-6 flex items-center justify-between">
            <div class="flex gap-4 text-sm">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-lask-accent rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">学習</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-lask-1 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">作業</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-lask-4 rounded"></div>
                    <span class="text-gray-600 dark:text-gray-400">休憩</span>
                </div>
            </div>
            <a href="{{ route('planning.index') }}" class="text-sm text-lask-1 hover:underline">
                ← ダッシュボードに戻る
            </a>
        </div>
    </div>
</x-app-layout>
