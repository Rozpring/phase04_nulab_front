{{-- 
    課題詳細モーダル
    Backlogリンクと関連計画一覧
--}}
<div 
    x-data="{
        issue: {},
        
        init() {
            this.$watch('$store.ui.modals.issueDetail', (modal) => {
                if (modal.open && modal.issue) {
                    this.issue = modal.issue;
                }
            });
        },
        
        getRelatedPlans() {
            return $store.plans.items.filter(p => p.issue_key === this.issue.issue_key);
        },
        
        close() {
            $store.ui.closeModal('issueDetail');
        }
    }"
    x-show="$store.ui.modals.issueDetail.open"
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
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        @click.stop
    >
        {{-- ヘッダー --}}
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-mono px-2 py-1 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300" x-text="issue.issue_key"></span>
                    <span 
                        class="text-xs px-2 py-1 rounded-full"
                        :class="{
                            'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300': issue.priority === '高',
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300': issue.priority === '中',
                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': issue.priority === '低'
                        }"
                        x-text="issue.priority"
                    ></span>
                    <span 
                        class="text-xs px-2 py-1 rounded-full"
                        :class="{
                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': issue.status === '未対応',
                            'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300': issue.status === '処理中',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300': issue.status === '完了'
                        }"
                        x-text="issue.status"
                    ></span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100" x-text="issue.summary"></h2>
            </div>
            <button @click="close()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition ml-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        {{-- コンテンツ --}}
        <div class="p-6 space-y-6">
            {{-- 詳細情報 --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">種別</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="issue.issue_type || '-'"></div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">期限</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="issue.due_date || '-'"></div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">予定時間</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        <span x-text="issue.estimated_hours ? issue.estimated_hours + '時間' : '-'"></span>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Backlog</div>
                    <a 
                        :href="issue.backlog_url"
                        target="_blank"
                        class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                    >
                        開く
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            {{-- 説明 --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">説明</h3>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap" x-text="issue.description || '説明がありません'"></p>
                </div>
            </div>
            
            {{-- 関連する計画 --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                    <x-icon name="calendar" class="w-4 h-4" />
                    関連する計画
                </h3>
                <div class="space-y-2">
                    <template x-for="plan in getRelatedPlans()" :key="plan.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <template x-if="plan.plan_type === 'study'">
                                    <x-icon name="book-open" class="w-5 h-5 text-indigo-500" />
                                </template>
                                <template x-if="plan.plan_type === 'work'">
                                    <x-icon name="briefcase" class="w-5 h-5 text-sky-500" />
                                </template>
                                <template x-if="plan.plan_type === 'break'">
                                    <x-icon name="sun" class="w-5 h-5 text-amber-500" />
                                </template>
                                <template x-if="plan.plan_type === 'review'">
                                    <x-icon name="arrow-path" class="w-5 h-5 text-purple-500" />
                                </template>
                                <template x-if="!['study', 'work', 'break', 'review'].includes(plan.plan_type)">
                                    <x-icon name="clipboard-document-list" class="w-5 h-5 text-gray-500" />
                                </template>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="plan.title"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="plan.scheduled_date"></span> · 
                                        <span x-text="plan.scheduled_time + ' - ' + plan.end_time"></span>
                                    </div>
                                </div>
                            </div>
                            <span 
                                class="text-xs px-2 py-1 rounded-full"
                                :class="{
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300': plan.status === 'planned',
                                    'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300': plan.status === 'in_progress',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300': plan.status === 'completed',
                                    'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300': plan.status === 'skipped'
                                }"
                                x-text="{ planned: '予定', in_progress: '進行中', completed: '完了', skipped: 'スキップ' }[plan.status]"
                            ></span>
                        </div>
                    </template>
                    <template x-if="getRelatedPlans().length === 0">
                        <div class="text-center py-6 text-gray-400 dark:text-gray-500">
                            <p class="text-sm">この課題に関連する計画はありません</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        {{-- フッター --}}
        <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
            <a 
                :href="issue.backlog_url"
                target="_blank"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium flex items-center gap-2"
            >
                Backlogで開く
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </div>
</div>
