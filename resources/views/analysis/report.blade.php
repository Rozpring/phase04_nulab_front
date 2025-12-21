<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('週間・月間レポート') }}
            </h2>
            <a href="{{ route('analysis.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                ← 分析ダッシュボード
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div 
            x-data="{
                weeklyData: [
                    { day: '月', date: '12/09', study: 2, work: 1.5, break: 0.5, completed: 4, total: 5 },
                    { day: '火', date: '12/10', study: 1.5, work: 2, break: 0.5, completed: 3, total: 4 },
                    { day: '水', date: '12/11', study: 3, work: 1, break: 0.5, completed: 5, total: 5 },
                    { day: '木', date: '12/12', study: 2.5, work: 1.5, break: 0.5, completed: 4, total: 6 },
                    { day: '金', date: '12/13', study: 2, work: 2, break: 0.5, completed: 3, total: 4 },
                    { day: '土', date: '12/14', study: 1, work: 0, break: 0.5, completed: 2, total: 2 },
                    { day: '日', date: '12/15', study: 0.5, work: 0, break: 0, completed: 1, total: 1 }
                ],
                categoryData: [
                    { category: 'study', label: '学習', hours: 12.5, color: '#6366f1' },
                    { category: 'work', label: '作業', hours: 8, color: '#0ea5e9' },
                    { category: 'break', label: '休憩', hours: 3, color: '#f59e0b' }
                ],
                
                get totalHours() {
                    return this.weeklyData.reduce((sum, d) => sum + d.study + d.work + d.break, 0);
                },
                
                get completionRate() {
                    const completed = this.weeklyData.reduce((sum, d) => sum + d.completed, 0);
                    const total = this.weeklyData.reduce((sum, d) => sum + d.total, 0);
                    return total > 0 ? Math.round((completed / total) * 100) : 0;
                },
                
                get maxDailyHours() {
                    return Math.max(...this.weeklyData.map(d => d.study + d.work + d.break));
                },
                
                getBarHeight(day) {
                    const total = day.study + day.work + day.break;
                    return this.maxDailyHours > 0 ? (total / this.maxDailyHours) * 100 : 0;
                }
            }"
            class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8"
        >
            {{-- サマリーカード --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-l-4 border-indigo-500">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400" x-text="totalHours.toFixed(1) + 'h'"></div>
                    <div class="text-gray-600 dark:text-gray-400 mt-1">今週の学習時間</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-l-4 border-emerald-500">
                    <div class="text-4xl font-bold text-emerald-600 dark:text-emerald-400" x-text="completionRate + '%'"></div>
                    <div class="text-gray-600 dark:text-gray-400 mt-1">完了率</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-l-4 border-sky-500">
                    <div class="text-4xl font-bold text-sky-600 dark:text-sky-400">27</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-1">完了タスク</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-l-4 border-amber-500">
                    <div class="text-4xl font-bold text-amber-600 dark:text-amber-400">5</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-1">連続日数</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- 週間学習時間グラフ --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <x-icon name="chart-bar" class="w-6 h-6 mr-2 text-indigo-500" />
                            週間学習時間
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-end justify-between h-48 gap-2">
                            <template x-for="day in weeklyData" :key="day.day">
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full flex flex-col-reverse" style="height: 160px;">
                                        {{-- 積み上げバー --}}
                                        <div 
                                            class="w-full transition-all duration-500 rounded-t flex flex-col-reverse overflow-hidden"
                                            :style="`height: ${getBarHeight(day)}%`"
                                        >
                                            <div class="bg-indigo-500" :style="`height: ${(day.study / (day.study + day.work + day.break)) * 100}%`"></div>
                                            <div class="bg-sky-500" :style="`height: ${(day.work / (day.study + day.work + day.break)) * 100}%`"></div>
                                            <div class="bg-amber-500" :style="`height: ${(day.break / (day.study + day.work + day.break)) * 100}%`"></div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="day.day"></div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500" x-text="day.date"></div>
                                </div>
                            </template>
                        </div>
                        {{-- 凡例 --}}
                        <div class="flex justify-center gap-6 mt-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-indigo-500 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">学習</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-sky-500 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">作業</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-amber-500 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">休憩</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- カテゴリ別円グラフ --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <x-icon name="chart-pie" class="w-6 h-6 mr-2 text-purple-500" />
                            カテゴリ別
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="relative w-48 h-48 mx-auto mb-4">
                            @php
                                $total = 23.5;
                                $segments = [
                                    ['label' => '学習', 'value' => 12.5, 'color' => '#6366f1'],
                                    ['label' => '作業', 'value' => 8, 'color' => '#0ea5e9'],
                                    ['label' => '休憩', 'value' => 3, 'color' => '#f59e0b'],
                                ];
                                $currentAngle = -90;
                            @endphp
                            <svg viewBox="0 0 200 200" class="w-full h-full">
                                @foreach ($segments as $segment)
                                    @php
                                        $percentage = $segment['value'] / $total;
                                        $angle = $percentage * 360;
                                        $largeArc = $angle > 180 ? 1 : 0;
                                        
                                        $startRad = deg2rad($currentAngle);
                                        $endRad = deg2rad($currentAngle + $angle);
                                        
                                        $startX = 100 + 80 * cos($startRad);
                                        $startY = 100 + 80 * sin($startRad);
                                        $endX = 100 + 80 * cos($endRad);
                                        $endY = 100 + 80 * sin($endRad);
                                        
                                        $midAngle = $currentAngle + $angle / 2;
                                        $labelRad = deg2rad($midAngle);
                                        $labelX = 100 + 50 * cos($labelRad);
                                        $labelY = 100 + 50 * sin($labelRad);
                                        
                                        $currentAngle += $angle;
                                    @endphp
                                    <path 
                                        d="M 100 100 L {{ $startX }} {{ $startY }} A 80 80 0 {{ $largeArc }} 1 {{ $endX }} {{ $endY }} Z"
                                        fill="{{ $segment['color'] }}"
                                        stroke="white"
                                        stroke-width="2"
                                        class="hover:opacity-80 transition-opacity cursor-pointer"
                                    />
                                    @if ($percentage >= 0.1)
                                        <text 
                                            x="{{ $labelX }}" 
                                            y="{{ $labelY }}" 
                                            text-anchor="middle" 
                                            dominant-baseline="middle"
                                            fill="white"
                                            font-size="12"
                                            font-weight="500"
                                        >{{ round($percentage * 100) }}%</text>
                                    @endif
                                @endforeach
                            </svg>
                        </div>
                        {{-- カテゴリリスト --}}
                        <div class="space-y-3">
                            <template x-for="cat in categoryData" :key="cat.category">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded" :style="`background-color: ${cat.color}`"></div>
                                        <span class="text-gray-700 dark:text-gray-300" x-text="cat.label"></span>
                                    </div>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="cat.hours + 'h'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 達成率トレンド --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <x-icon name="arrow-trending-up" class="w-6 h-6 mr-2 text-emerald-500" />
                            達成率トレンド（過去4週間）
                        </h3>
                </div>
                <div class="p-6">
                    <div class="relative h-48">
                        @php
                            $trendData = [65, 72, 80, 85];
                            $weeks = ['3週前', '2週前', '先週', '今週'];
                            $maxRate = 100;
                        @endphp
                        <svg viewBox="0 0 400 160" class="w-full h-full">
                            {{-- グリッド線 --}}
                            <line x1="50" y1="10" x2="50" y2="140" stroke="#e5e7eb" stroke-width="1"/>
                            <line x1="50" y1="140" x2="380" y2="140" stroke="#e5e7eb" stroke-width="1"/>
                            <line x1="50" y1="75" x2="380" y2="75" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="4"/>
                            <line x1="50" y1="10" x2="380" y2="10" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="4"/>
                            
                            {{-- Y軸ラベル --}}
                            <text x="40" y="15" text-anchor="end" fill="#9ca3af" font-size="10">100%</text>
                            <text x="40" y="77" text-anchor="end" fill="#9ca3af" font-size="10">50%</text>
                            <text x="40" y="145" text-anchor="end" fill="#9ca3af" font-size="10">0%</text>
                            
                            {{-- 折れ線 --}}
                            <polyline 
                                points="@foreach($trendData as $i => $rate){{ 100 + $i * 90 }},{{ 140 - ($rate / $maxRate) * 130 }} @endforeach"
                                fill="none"
                                stroke="#6366f1"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            
                            {{-- データポイント --}}
                            @foreach($trendData as $i => $rate)
                                <circle 
                                    cx="{{ 100 + $i * 90 }}" 
                                    cy="{{ 140 - ($rate / $maxRate) * 130 }}" 
                                    r="6" 
                                    fill="#6366f1"
                                    stroke="white"
                                    stroke-width="2"
                                />
                                <text 
                                    x="{{ 100 + $i * 90 }}" 
                                    y="{{ 140 - ($rate / $maxRate) * 130 - 12 }}" 
                                    text-anchor="middle" 
                                    fill="#6366f1" 
                                    font-size="12"
                                    font-weight="bold"
                                >{{ $rate }}%</text>
                                <text 
                                    x="{{ 100 + $i * 90 }}" 
                                    y="155" 
                                    text-anchor="middle" 
                                    fill="#9ca3af" 
                                    font-size="11"
                                >{{ $weeks[$i] }}</text>
                            @endforeach
                        </svg>
                    </div>
                    <div class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1">
                        <x-icon name="arrow-trending-up" class="w-4 h-4 text-emerald-500" />
                        達成率が <span class="text-emerald-600 dark:text-emerald-400 font-bold">+20%</span> 向上しました！
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
