{{-- 通知設定パネル --}}
<div 
    x-data="{ 
        open: false,
        get notifications() { return Alpine.store('notifications') }
    }"
    class="relative"
>
    {{-- 通知ボタン --}}
    <button 
        @click="open = !open"
        class="p-2 rounded-lg transition relative"
        :class="{
            'text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30': notifications.permission === 'granted' && notifications.enabled,
            'text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700': notifications.permission !== 'granted' || !notifications.enabled
        }"
        title="通知設定"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        {{-- アクティブインジケーター --}}
        <span 
            x-show="notifications.permission === 'granted' && notifications.enabled"
            class="absolute top-1 right-1 w-2 h-2 bg-green-500 rounded-full"
        ></span>
    </button>

    {{-- 設定ドロップダウン --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50"
        style="display: none;"
    >
        {{-- ヘッダー --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <x-icon name="bell" class="w-5 h-5 text-indigo-500" />
                    通知設定
                </h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4 space-y-4">
            {{-- 通知許可ステータス --}}
            <div class="p-3 rounded-lg" :class="{
                'bg-green-50 dark:bg-green-900/20': notifications.permission === 'granted',
                'bg-yellow-50 dark:bg-yellow-900/20': notifications.permission === 'default',
                'bg-red-50 dark:bg-red-900/20': notifications.permission === 'denied'
            }">
                <template x-if="notifications.permission === 'granted'">
                    <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-medium">通知が許可されています</span>
                    </div>
                </template>
                <template x-if="notifications.permission === 'default'">
                    <div>
                        <div class="flex items-center gap-2 text-yellow-700 dark:text-yellow-300 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-medium">通知を許可してください</span>
                        </div>
                        <button 
                            @click="notifications.requestPermission()"
                            class="w-full px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition"
                        >
                            通知を許可する
                        </button>
                    </div>
                </template>
                <template x-if="notifications.permission === 'denied'">
                    <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <span class="text-sm font-medium">通知がブロックされています</span>
                    </div>
                </template>
            </div>

            {{-- 通知の有効/無効切り替え --}}
            <div 
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                x-show="notifications.permission === 'granted'"
            >
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">通知を有効にする</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">計画のリマインダーを受け取る</p>
                </div>
                <button 
                    @click="notifications.toggle()"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="notifications.enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                >
                    <span 
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="notifications.enabled ? 'translate-x-5' : 'translate-x-0'"
                    ></span>
                </button>
            </div>

            {{-- 詳細設定 --}}
            <div x-show="notifications.permission === 'granted' && notifications.enabled" class="space-y-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">通知タイプ</p>
                
                {{-- 計画開始通知 --}}
                <label class="flex items-center justify-between cursor-pointer">
                    <div class="flex items-center gap-2">
                        <x-icon name="book-open" class="w-5 h-5 text-indigo-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">計画開始時刻</span>
                    </div>
                    <input 
                        type="checkbox" 
                        :checked="notifications.settings.planStart"
                        @change="notifications.updateSettings({ planStart: $event.target.checked })"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                </label>

                {{-- リマインダー --}}
                <label class="flex items-center justify-between cursor-pointer">
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" class="w-5 h-5 text-amber-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">5分前リマインダー</span>
                    </div>
                    <input 
                        type="checkbox" 
                        :checked="notifications.settings.planReminder"
                        @change="notifications.updateSettings({ planReminder: $event.target.checked })"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                </label>

                {{-- ポモドーロ終了 --}}
                <label class="flex items-center justify-between cursor-pointer">
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" class="w-5 h-5 text-rose-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">ポモドーロ終了</span>
                    </div>
                    <input 
                        type="checkbox" 
                        :checked="notifications.settings.pomodoroEnd"
                        @change="notifications.updateSettings({ pomodoroEnd: $event.target.checked })"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                </label>
            </div>

            {{-- テスト通知ボタン --}}
            <button 
                x-show="notifications.permission === 'granted' && notifications.enabled"
                @click="notifications.send('テスト通知', { body: 'NextLogからの通知が正常に動作しています!' }); notifications.showToast('テスト通知を送信しました', 'success')"
                class="w-full px-3 py-2 text-sm text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition"
            >
                テスト通知を送信
            </button>
        </div>
    </div>
</div>
