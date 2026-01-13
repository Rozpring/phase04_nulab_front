{{-- 
    カンバンボード（SortableJS版）
    ドラッグ&ドロップでステータス変更 + 同一カラム内並び替え
--}}
@props(['plans' => []])

<div 
    x-data="kanbanBoard()"
    x-init="$store.plans.init()"
    {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 xl:gap-6']) }}
>
    {{-- 予定カラム --}}
    <div class="rounded-xl border-2 transition-all duration-200 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600"
         :class="{ 'ring-2 ring-lask-1 scale-[1.02]': dragOverColumn === 'planned' }">
        <div class="p-4 border-b border-gray-300 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">予定</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                      x-text="getPlansForStatus('planned').length"></span>
            </div>
        </div>
        <div class="p-3 min-h-[200px] space-y-3" id="column-planned" data-status="planned" x-init="initColumn('planned')">
            <template x-for="plan in getPlansForStatus('planned')" :key="plan.id">
                <div class="kanban-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 cursor-grab active:cursor-grabbing"
                     :data-id="plan.id"
                     draggable="true"
                     @dragstart="onDragStart($event, plan)"
                     @dragend="onDragEnd($event)">
                    <div class="p-3">
                        <div class="flex items-start gap-2">
                            <div x-html="getPlanTypeIcon(plan.plan_type)" class="flex-shrink-0 w-5 h-5"></div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate" x-text="plan.title"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="plan.scheduled_time + ' - ' + plan.end_time"></p>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button @click="changeStatus(plan.id, 'in_progress')"
                                    class="text-xs px-2 py-1 rounded bg-lask-accent-subtle text-lask-1 hover:bg-lask-accent/20 transition">
                                ▶ 開始
                            </button>
                            <button @click="changeStatus(plan.id, 'completed')"
                                    class="text-xs px-2 py-1 rounded bg-lask-success-light text-lask-success hover:bg-lask-success/20 transition">
                                <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                完了
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="getPlansForStatus('planned').length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
                <p>計画なし</p>
            </div>
        </div>
    </div>

    {{-- 進行中カラム --}}
    <div class="rounded-xl border-2 transition-all duration-200 bg-sky-50 dark:bg-sky-900/20 border-sky-300 dark:border-sky-600"
         :class="{ 'ring-2 ring-lask-1 scale-[1.02]': dragOverColumn === 'in_progress' }"
         @dragover.prevent="dragOverColumn = 'in_progress'"
         @dragleave="dragOverColumn = null"
         @drop.prevent="onDrop($event, 'in_progress')">
        <div class="p-4 border-b border-sky-300 dark:border-sky-600">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">進行中</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                      x-text="getPlansForStatus('in_progress').length"></span>
            </div>
        </div>
        <div class="p-3 min-h-[200px] space-y-3" id="column-in_progress" data-status="in_progress">
            <template x-for="plan in getPlansForStatus('in_progress')" :key="plan.id">
                <div class="kanban-card bg-white dark:bg-gray-800 rounded-lg border-2 border-lask-1 shadow-sm hover:shadow-md transition-all duration-200 cursor-grab active:cursor-grabbing relative"
                     :data-id="plan.id"
                     draggable="true"
                     @dragstart="onDragStart($event, plan)"
                     @dragend="onDragEnd($event)">
                    {{-- 進行中インジケーター（パルスアニメーション） --}}
                    <div class="absolute top-2 right-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lask-success opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-lask-success"></span>
                        </span>
                    </div>
                    <div class="p-3">
                        <div class="flex items-start gap-2">
                            <template x-if="plan.plan_type === 'study'">
                                <x-icon name="book-open" class="w-5 h-5 flex-shrink-0 text-lask-1" />
                            </template>
                            <template x-if="plan.plan_type === 'work'">
                                <x-icon name="briefcase" class="w-5 h-5 flex-shrink-0 text-lask-1" />
                            </template>
                            <template x-if="plan.plan_type === 'break'">
                                <x-icon name="sun" class="w-5 h-5 flex-shrink-0 text-lask-4" />
                            </template>
                            <template x-if="plan.plan_type === 'review'">
                                <x-icon name="arrow-path" class="w-5 h-5 flex-shrink-0 text-lask-3" />
                            </template>
                            <template x-if="!['study', 'work', 'break', 'review'].includes(plan.plan_type)">
                                <x-icon name="clipboard-document-list" class="w-5 h-5 flex-shrink-0 text-gray-500" />
                            </template>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate" x-text="plan.title"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="plan.scheduled_time + ' - ' + plan.end_time"></p>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button @click="changeStatus(plan.id, 'completed')"
                                    class="text-xs px-2 py-1 rounded bg-lask-success-light text-lask-success hover:bg-lask-success/20 transition">
                                <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                完了
                            </button>
                            <button @click="changeStatus(plan.id, 'skipped')"
                                    class="text-xs px-2 py-1 rounded bg-rose-100 text-rose-600 hover:bg-rose-200 dark:bg-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-900 transition">
                                スキップ
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="getPlansForStatus('in_progress').length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
                <x-icon name="play" class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                <p>進行中のタスクなし</p>
            </div>
        </div>
    </div>

    {{-- 完了カラム --}}
    <div class="rounded-xl border-2 transition-all duration-200 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-600"
         :class="{ 'ring-2 ring-lask-1 scale-[1.02]': dragOverColumn === 'completed' }"
         @dragover.prevent="dragOverColumn = 'completed'"
         @dragleave="dragOverColumn = null"
         @drop.prevent="onDrop($event, 'completed')">
        <div class="p-4 border-b border-emerald-300 dark:border-emerald-600">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">完了</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                      x-text="getPlansForStatus('completed').length"></span>
            </div>
        </div>
        <div class="p-3 min-h-[200px] space-y-3" id="column-completed" data-status="completed">
            <template x-for="plan in getPlansForStatus('completed')" :key="plan.id">
                <div class="kanban-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200"
                     :data-id="plan.id"
                     draggable="true"
                     @dragstart="onDragStart($event, plan)"
                     @dragend="onDragEnd($event)">
                    <div class="p-3">
                        <div class="flex items-start gap-2">
                            <x-icon name="check-circle" class="w-5 h-5 text-lask-success flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate line-through opacity-70" x-text="plan.title"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="plan.scheduled_time + ' - ' + plan.end_time"></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button @click="changeStatus(plan.id, 'planned')"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 transition">
                                ↩ 予定に戻す
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="getPlansForStatus('completed').length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
                <x-icon name="check-circle" class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                <p>完了したタスクなし</p>
            </div>
        </div>
    </div>

    {{-- スキップカラム --}}
    <div class="rounded-xl border-2 transition-all duration-200 bg-rose-50 dark:bg-rose-900/20 border-rose-300 dark:border-rose-600"
         :class="{ 'ring-2 ring-lask-1 scale-[1.02]': dragOverColumn === 'skipped' }"
         @dragover.prevent="dragOverColumn = 'skipped'"
         @dragleave="dragOverColumn = null"
         @drop.prevent="onDrop($event, 'skipped')">
        <div class="p-4 border-b border-rose-300 dark:border-rose-600">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">スキップ</h3>
                <span class="text-xs px-2 py-1 rounded-full bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                      x-text="getPlansForStatus('skipped').length"></span>
            </div>
        </div>
        <div class="p-3 min-h-[200px] space-y-3" id="column-skipped" data-status="skipped">
            <template x-for="plan in getPlansForStatus('skipped')" :key="plan.id">
                <div class="kanban-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200"
                     :data-id="plan.id"
                     draggable="true"
                     @dragstart="onDragStart($event, plan)"
                     @dragend="onDragEnd($event)">
                    <div class="p-3">
                        <div class="flex items-start gap-2">
                            <x-icon name="forward" class="w-5 h-5 text-rose-500 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate opacity-70" x-text="plan.title"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="plan.scheduled_time + ' - ' + plan.end_time"></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button @click="changeStatus(plan.id, 'planned')"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 transition">
                                ↩ 予定に戻す
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="getPlansForStatus('skipped').length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
                <p>スキップしたタスクなし</p>
            </div>
        </div>
    </div>
</div>

<style>
    .sortable-ghost {
        opacity: 0.4;
        background: rgb(99, 102, 241) !important;
        border: 2px dashed rgb(99, 102, 241) !important;
    }
    .kanban-card.dragging {
        opacity: 0.5;
        transform: rotate(3deg);
    }
</style>

<script>
    function kanbanBoard() {
        return {
            dragOverColumn: null,
            draggedPlan: null,
            
            // Heroicons SVG for plan types - using CSS variables for theme support
            getPlanTypeIcon(type) {
                const icons = {
                    study: '<svg class="w-5 h-5" style="color: var(--color-accent)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>',
                    work: '<svg class="w-5 h-5" style="color: var(--color-1)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/></svg>',
                    break: '<svg class="w-5 h-5" style="color: var(--color-4)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z"/></svg>',
                    review: '<svg class="w-5 h-5" style="color: var(--color-3)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>'
                };
                return icons[type] || '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>';
            },
            
            getPlansForStatus(status) {
                return Alpine.store('plans').getByStatus(status);
            },
            
            initColumn(status) {
                // 予定カラムにドロップイベントを追加
                const el = document.getElementById('column-' + status);
                if (el) {
                    el.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        this.dragOverColumn = status;
                    });
                    el.addEventListener('dragleave', () => {
                        this.dragOverColumn = null;
                    });
                    el.addEventListener('drop', (e) => {
                        e.preventDefault();
                        this.onDrop(e, status);
                    });
                }
            },
            
            onDragStart(e, plan) {
                this.draggedPlan = plan;
                e.target.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', plan.id.toString());
            },
            
            onDragEnd(e) {
                e.target.classList.remove('dragging');
                this.draggedPlan = null;
                this.dragOverColumn = null;
            },
            
            onDrop(e, newStatus) {
                e.preventDefault();
                
                if (this.draggedPlan && this.draggedPlan.status !== newStatus) {
                    this.changeStatus(this.draggedPlan.id, newStatus);
                }
                
                this.draggedPlan = null;
                this.dragOverColumn = null;
            },
            
            changeStatus(id, newStatus) {
                const statusLabels = {
                    'planned': '予定',
                    'in_progress': '進行中',
                    'completed': '完了',
                    'skipped': 'スキップ'
                };
                
                Alpine.store('plans').updateStatus(id, newStatus);
                Alpine.store('notifications').showToast(
                    `「${statusLabels[newStatus]}」に移動しました`,
                    newStatus === 'completed' ? 'success' : 'info'
                );
            }
        };
    }
</script>
