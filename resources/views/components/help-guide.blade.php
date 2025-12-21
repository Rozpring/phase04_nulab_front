{{-- 
    アプリ使用方法ヘルプガイドモーダル
--}}
<div 
    x-show="$store.ui.modals.help.open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="$store.ui.closeModal('help')"
    style="display: none;"
>
    {{-- オーバーレイ --}}
    <div class="absolute inset-0 bg-black/50" @click="$store.ui.closeModal('help')"></div>
    
    {{-- モーダルコンテンツ --}}
    <div 
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        @click.stop
    >
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-t-2xl">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <x-icon name="book-open" class="w-6 h-6" />
                LASKの使い方
            </h2>
            <button @click="$store.ui.closeModal('help')" class="p-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        {{-- コンテンツ --}}
        <div class="p-6 space-y-6">
            {{-- イントロ --}}
            <div class="text-center pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">LASKへようこそ！</h3>
                <p class="text-gray-600 dark:text-gray-400">BacklogとAIで効率的な学習計画を自動生成</p>
            </div>
            
            {{-- 機能紹介カード --}}
            <div class="grid gap-4">
                {{-- 1. Backlog連携 --}}
                <div class="flex gap-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                    <x-icon name="inbox-arrow-down" class="w-8 h-8 text-indigo-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">1. Backlogから課題をインポート</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Backlog設定でAPIキーを登録し、課題を取り込みます。優先度や期限も自動で取得されます。</p>
                    </div>
                </div>
                
                {{-- 2. AI計画生成 --}}
                <div class="flex gap-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <x-icon name="cpu-chip" class="w-8 h-8 text-purple-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">2. AI計画生成</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">「計画を生成」ボタンで、優先度と期限を考慮した最適なスケジュールを自動生成します。</p>
                    </div>
                </div>
                
                {{-- 3. 計画管理 --}}
                <div class="flex gap-4 p-4 bg-sky-50 dark:bg-sky-900/20 rounded-xl">
                    <x-icon name="clipboard-document-list" class="w-8 h-8 text-sky-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">3. 計画を管理</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">計画カードから開始・完了・スキップの操作が可能。編集や新規追加もできます。</p>
                    </div>
                </div>
                
                {{-- 4. ポモドーロ --}}
                <div class="flex gap-4 p-4 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                    <x-icon name="clock" class="w-8 h-8 text-rose-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">4. ポモドーロタイマー</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">25分作業 + 5分休憩のサイクルで集中力を維持。計画開始時に自動でタイマーが動きます。</p>
                    </div>
                </div>
                
                {{-- 5. 進捗分析 --}}
                <div class="flex gap-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                    <x-icon name="chart-bar" class="w-8 h-8 text-emerald-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">5. 進捗分析</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">完了率や失敗パターンを分析し、AIがアドバイスを提供します。</p>
                    </div>
                </div>
            </div>
            
            {{-- ヒント --}}
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                <div class="flex items-start gap-3">
                    <x-icon name="light-bulb" class="w-6 h-6 text-amber-500 flex-shrink-0" />
                    <div>
                        <h4 class="font-semibold text-amber-800 dark:text-amber-200 mb-1">ヒント</h4>
                        <ul class="text-sm text-amber-700 dark:text-amber-300 space-y-1">
                            <li>• <kbd class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900 rounded text-xs">?</kbd> キーでショートカット一覧を表示</li>
                            <li>• <kbd class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900 rounded text-xs">T</kbd> キーでダークモード切り替え</li>
                            <li>• カードをドラッグ&ドロップでステータス変更</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- フッター --}}
        <div class="flex items-center justify-center px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
            <button 
                @click="$store.ui.closeModal('help')"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium flex items-center gap-2"
            >
                <x-icon name="rocket-launch" class="w-4 h-4" />
                始める
            </button>
        </div>
    </div>
</div>
