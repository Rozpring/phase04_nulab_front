{{-- 
    ポモドーロタイマー
    25分作業 + 5分休憩
--}}
<div 
    x-data="{
        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }
    }"
    x-init="requestNotificationPermission()"
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
>
    {{-- ヘッダー --}}
    <div 
        class="px-4 py-3 flex items-center justify-between"
        :class="$store.pomodoro.isBreak ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-indigo-50 dark:bg-indigo-900/20'"
    >
        <div class="flex items-center gap-2">
            <template x-if="$store.pomodoro.isBreak">
                <x-icon name="sun" class="w-5 h-5 text-amber-500" />
            </template>
            <template x-if="!$store.pomodoro.isBreak">
                <x-icon name="clock" class="w-5 h-5 text-indigo-500" />
            </template>
            <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="$store.pomodoro.isBreak ? '休憩タイム' : 'ポモドーロ'"></span>
        </div>
        <div class="text-xs px-2 py-1 rounded-full" :class="$store.pomodoro.isBreak ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'">
            <span x-text="$store.pomodoro.isBreak ? '5分' : '25分'"></span>
        </div>
    </div>
    
    {{-- タイマー表示 --}}
    <div class="p-6 text-center">
        <div 
            class="text-6xl font-mono font-bold mb-4"
            :class="$store.pomodoro.isBreak ? 'text-amber-600 dark:text-amber-400' : 'text-indigo-600 dark:text-indigo-400'"
            x-text="$store.pomodoro.formattedTime"
        ></div>
        
        {{-- プログレスバー --}}
        <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
            <div 
                class="h-full transition-all duration-1000 rounded-full"
                :class="$store.pomodoro.isBreak ? 'bg-amber-500' : 'bg-indigo-500'"
                :style="`width: ${((($store.pomodoro.isBreak ? $store.pomodoro.breakDuration : $store.pomodoro.workDuration) - $store.pomodoro.timeLeft) / ($store.pomodoro.isBreak ? $store.pomodoro.breakDuration : $store.pomodoro.workDuration)) * 100}%`"
            ></div>
        </div>
        
        {{-- コントロールボタン --}}
        <div class="flex items-center justify-center gap-3">
            {{-- スタート/一時停止 --}}
            <button
                x-show="!$store.pomodoro.isRunning"
                @click="$store.pomodoro.isRunning ? $store.pomodoro.pause() : ($store.pomodoro.timeLeft > 0 ? $store.pomodoro.resume() : $store.pomodoro.start())"
                class="p-4 rounded-full transition shadow-lg"
                :class="$store.pomodoro.isBreak ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-indigo-500 hover:bg-indigo-600 text-white'"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </button>
            
            <button
                x-show="$store.pomodoro.isRunning"
                @click="$store.pomodoro.pause()"
                class="p-4 rounded-full transition shadow-lg"
                :class="$store.pomodoro.isBreak ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-indigo-500 hover:bg-indigo-600 text-white'"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 4h4v16H6zM14 4h4v16h-4z"/>
                </svg>
            </button>
            
            {{-- リセット --}}
            <button
                @click="$store.pomodoro.reset()"
                class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            
            {{-- スキップ --}}
            <button
                @click="$store.pomodoro.onComplete()"
                class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 transition"
                title="次へスキップ"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
        
        {{-- ヒント --}}
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            スペースキーで開始/一時停止
        </p>
    </div>
</div>
