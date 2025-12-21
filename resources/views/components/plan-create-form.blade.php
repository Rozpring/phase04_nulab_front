{{-- 
    新規計画作成フォーム
--}}
<div 
    x-data="{
        plan: {
            title: '',
            description: '',
            plan_type: 'study',
            scheduled_date: new Date().toISOString().split('T')[0],
            scheduled_time: '09:00',
            end_time: '10:00',
            duration_minutes: 60
        },
        
        reset() {
            this.plan = {
                title: '',
                description: '',
                plan_type: 'study',
                scheduled_date: new Date().toISOString().split('T')[0],
                scheduled_time: '09:00',
                end_time: '10:00',
                duration_minutes: 60
            };
        },
        
        calculateDuration() {
            const [startH, startM] = this.plan.scheduled_time.split(':').map(Number);
            const [endH, endM] = this.plan.end_time.split(':').map(Number);
            const diff = (endH * 60 + endM) - (startH * 60 + startM);
            this.plan.duration_minutes = diff > 0 ? diff : 0;
        },
        
        save() {
            if (!this.plan.title?.trim()) {
                alert('タイトルを入力してください');
                return;
            }
            this.calculateDuration();
            $store.plans.add(this.plan);
            this.reset();
            this.close();
        },
        
        close() {
            $store.ui.closeModal('planCreate');
        }
    }"
    x-show="$store.ui.modals.planCreate.open"
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
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-icon name="plus" class="w-5 h-5 text-lask-1" />
                新しい計画
            </h2>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">タイトル <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    x-model="plan.title"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-lask-accent focus:border-transparent"
                    placeholder="何を計画しますか？"
                    autofocus
                >
            </div>
            
            {{-- 説明 --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">説明（任意）</label>
                <textarea 
                    x-model="plan.description"
                    rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-lask-accent focus:border-transparent resize-none"
                    placeholder="詳細な説明を追加"
                ></textarea>
            </div>
            
            {{-- タイプ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">タイプ</label>
                <div class="grid grid-cols-4 gap-2">
                    <button 
                        type="button"
                        @click="plan.plan_type = 'study'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'study' ? 'border-lask-1 bg-lask-accent-subtle' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="book-open" class="w-6 h-6 mx-auto mb-1 text-lask-1" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">学習</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'work'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'work' ? 'border-lask-1 bg-[var(--color-1)]/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="briefcase" class="w-6 h-6 mx-auto mb-1 text-lask-1" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">作業</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'break'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'break' ? 'border-lask-4 bg-[var(--color-4)]/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="sun" class="w-6 h-6 mx-auto mb-1 text-lask-4" />
                        <div class="text-xs text-gray-600 dark:text-gray-400">休憩</div>
                    </button>
                    <button 
                        type="button"
                        @click="plan.plan_type = 'review'"
                        class="p-3 rounded-lg border-2 text-center transition"
                        :class="plan.plan_type === 'review' ? 'border-lask-3 bg-[var(--color-3)]/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
                    >
                        <x-icon name="arrow-path" class="w-6 h-6 mx-auto mb-1 text-lask-3" />
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
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-lask-accent"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">開始</label>
                    <input 
                        type="time" 
                        x-model="plan.scheduled_time"
                        @change="calculateDuration()"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-lask-accent"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">終了</label>
                    <input 
                        type="time" 
                        x-model="plan.end_time"
                        @change="calculateDuration()"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-lask-accent"
                    >
                </div>
            </div>
            
            {{-- 所要時間表示 --}}
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
                所要時間: <span x-text="Math.floor(plan.duration_minutes / 60) + '時間' + (plan.duration_minutes % 60 ? (plan.duration_minutes % 60) + '分' : '')" class="font-medium"></span>
            </div>
        </div>
        
        {{-- フッター --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
            <button 
                @click="close()"
                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
            >
                キャンセル
            </button>
            <button 
                @click="save()"
                class="px-6 py-2 bg-lask-accent text-white rounded-lg hover:bg-lask-accent-hover transition font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                追加
            </button>
        </div>
    </div>
</div>
