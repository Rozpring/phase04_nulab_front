{{-- 
    計画編集モーダル
    タイトル、時間、タイプの編集と削除
--}}
<div 
    x-data="{
        plan: {},
        
        init() {
            this.$watch('$store.ui.modals.planEdit', (modal) => {
                if (modal.open && modal.plan) {
                    this.plan = { ...modal.plan };
                }
            });
        },
        
        save() {
            if (!this.plan.title?.trim()) {
                alert('タイトルを入力してください');
                return;
            }
            $store.plans.update(this.plan.id, this.plan);
            this.close();
        },
        
        remove() {
            if (confirm('この計画を削除しますか？')) {
                $store.plans.remove(this.plan.id);
                this.close();
            }
        },
        
        close() {
            $store.ui.closeModal('planEdit');
        }
    }"
    x-show="$store.ui.modals.planEdit.open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="close()"
    style="display: none;"
>
    {{-- オーバーレイ --}}
    <div class="absolute inset-0 bg-black/50" @click="close()"></div>
    
    {{-- モーダルコンテンツ --}}
    <div 
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        @click.stop
    >
        {{-- ヘッダー --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">計画を編集</h2>
            <button @click="close()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        {{-- フォーム --}}
        <div class="p-6 space-y-4">
            {{-- タイトル --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">タイトル</label>
                <input 
                    type="text" 
                    x-model="plan.title"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="計画のタイトル"
                >
            </div>
            
            {{-- 説明 --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">説明（任意）</label>
                <textarea 
                    x-model="plan.description"
                    rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                    placeholder="詳細な説明"
                ></textarea>
            </div>
            
            {{-- タイプ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">タイプ</label>
                <div class="grid grid-cols-4 gap-2">
                    <button 
                        type="button"
                        @click="plan.plan_type = 'study'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'study' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="book-open" class="w-6 h-6 mx-auto mb-1 text-indigo-500" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">学習</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'work'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'work' ? 'border-sky-500 bg-sky-50 dark:bg-sky-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="briefcase" class="w-6 h-6 mx-auto mb-1 text-sky-500" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">作業</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'break'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'break' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="sun" class="w-6 h-6 mx-auto mb-1 text-amber-500" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">休憩</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'review'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'review' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="arrow-path" class="w-6 h-6 mx-auto mb-1 text-purple-500" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">復習</div>
                    </button>
                </div>
            </div>
            
            {{-- 日付・時間 --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">日付</label>
                    <input 
                        type="date" 
                        x-model="plan.scheduled_date"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">開始</label>
                    <input 
                        type="time" 
                        x-model="plan.scheduled_time"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">終了</label>
                    <input 
                        type="time" 
                        x-model="plan.end_time"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            {{-- ステータス --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ステータス</label>
                <select 
                    x-model="plan.status"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="planned">予定</option>
                    <option value="in_progress">進行中</option>
                    <option value="completed">完了</option>
                    <option value="skipped">スキップ</option>
                </select>
            </div>
        </div>
        
        {{-- フッター --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
            <button 
                @click="remove()"
                class="px-4 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                削除
            </button>
            <div class="flex items-center gap-3">
                <button 
                    @click="close()"
                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                >
                    キャンセル
                </button>
                <button 
                    @click="save()"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium"
                >
                    保存
                </button>
            </div>
        </div>
    </div>
</div>
