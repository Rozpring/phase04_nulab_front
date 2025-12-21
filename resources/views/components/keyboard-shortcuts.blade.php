{{-- 
    キーボードショートカット一覧モーダル
--}}
<div 
    x-show="$store.ui.modals.shortcuts.open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="$store.ui.closeModal('shortcuts')"
    style="display: none;"
>
    {{-- オーバーレイ --}}
    <div class="absolute inset-0 bg-black/50" @click="$store.ui.closeModal('shortcuts')"></div>
    
    {{-- モーダルコンテンツ --}}
    <div 
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        @click.stop
    >
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-icon name="command-line" class="w-5 h-5" />
                キーボードショートカット
            </h2>
            <button @click="$store.ui.closeModal('shortcuts')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        {{-- ショートカット一覧 --}}
        <div class="p-6">
            <div class="space-y-1">
                {{-- ナビゲーション --}}
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">ナビゲーション</div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">ダッシュボードへ移動</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">⌘/Ctrl</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">D</kbd>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">計画ダッシュボードへ移動</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">⌘/Ctrl</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">P</kbd>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>
                
                {{-- アクション --}}
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">アクション</div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">新規計画を追加</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">⌘/Ctrl</kbd>
                        <span class="text-gray-400">+</span>
                        <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">N</kbd>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">タイマー開始/一時停止</span>
                    <kbd class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">Space</kbd>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>
                
                {{-- その他 --}}
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">その他</div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">テーマ切り替え</span>
                    <kbd class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">T</kbd>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">ヘルプを表示</span>
                    <kbd class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">H</kbd>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">このヘルプを表示</span>
                    <kbd class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">?</kbd>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-700 dark:text-gray-300">モーダルを閉じる</span>
                    <kbd class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-600 dark:text-gray-400">Esc</kbd>
                </div>
            </div>
        </div>
    </div>
</div>
