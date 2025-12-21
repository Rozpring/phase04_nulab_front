{{-- 
    インタラクティブ計画カード
    ステータス変更ボタン付き
--}}
@props(['plan', 'showActions' => true])

@php
    // テーマ連動カラー - 補色パレット使用
    $typeColors = [
        'study' => 'border-lask-accent bg-lask-accent-subtle',
        'work' => 'border-[#6b8cae] bg-[#6b8cae]/20',
        'break' => 'border-[#8fbc8f] bg-[#8fbc8f]/30',
        'review' => 'border-[#2c3e50] bg-[#2c3e50]/25',
    ];
    $typeIcons = [
        'study' => 'book-open',
        'work' => 'briefcase',
        'break' => 'sun',
        'review' => 'arrow-path',
    ];
    $typeIconColors = [
        'study' => 'text-lask-1',
        'work' => 'text-lask-1',
        'break' => 'text-lask-text-secondary',
        'review' => 'text-lask-3',
    ];
    $statusConfig = [
        'planned' => ['label' => '予定', 'color' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
        'in_progress' => ['label' => '進行中', 'color' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300'],
        'completed' => ['label' => '完了', 'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'],
        'skipped' => ['label' => 'スキップ', 'color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300'],
    ];
    
    $planData = is_array($plan) ? $plan : $plan->toArray();
    $planType = $planData['plan_type'] ?? 'study';
    $status = $planData['status'] ?? 'planned';
@endphp

<div 
    x-data="{ 
        plan: {{ Js::from($planData) }},
        showMenu: false,
        updateStatus(newStatus) {
            this.plan.status = newStatus;
            $store.plans.updateStatus(this.plan.id, newStatus);
            this.showMenu = false;
        },
        edit() {
            $store.ui.openModal('planEdit', { plan: this.plan });
        },
        remove() {
            if (confirm('この計画を削除しますか？')) {
                $store.plans.remove(this.plan.id);
                this.$el.remove();
            }
        },
        startPomodoro() {
            $store.pomodoro.start(this.plan.id);
            this.updateStatus('in_progress');
        }
    }"
    class="group p-4 border-l-4 {{ $typeColors[$planType] ?? 'border-gray-500 bg-gray-50' }} hover:shadow-md transition-all duration-200 relative"
    draggable="true"
    @dragstart="$event.dataTransfer.setData('text/plain', JSON.stringify(plan))"
>
    {{-- ドラッグハンドル --}}
    <div class="absolute left-0 top-0 bottom-0 w-6 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-grab transition-opacity">
        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
        </svg>
    </div>

    <div class="flex items-start justify-between pl-4">
        <div class="flex-1 min-w-0">
            {{-- 時間とタイプ --}}
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <x-icon :name="$typeIcons[$planType] ?? 'clipboard-document-list'" class="w-5 h-5 {{ $typeIconColors[$planType] ?? 'text-gray-500' }}" />
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400" x-text="plan.scheduled_time + ' - ' + plan.end_time">
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                    <span x-text="Math.floor(plan.duration_minutes / 60) + '時間' + (plan.duration_minutes % 60 ? (plan.duration_minutes % 60) + '分' : '')"></span>
                </span>
                <template x-if="plan.issue_key">
                    <span 
                        class="text-xs font-mono text-lask-1 cursor-pointer hover:underline"
                        @click="$store.ui.openModal('issueDetail', { issue: $store.issues.getByKey(plan.issue_key) })"
                        x-text="plan.issue_key"
                    ></span>
                </template>
            </div>
            
            {{-- タイトル --}}
            <h4 class="font-medium text-gray-900 dark:text-gray-100 truncate" x-text="plan.title"></h4>
            
            {{-- AI理由 --}}
            <template x-if="plan.ai_reason">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1 flex items-center gap-1">
                    <x-icon name="light-bulb" class="w-3 h-3 text-amber-500" />
                    <span x-text="plan.ai_reason"></span>
                </p>
            </template>
            
            {{-- 説明があればホバーで詳細表示 --}}
            <template x-if="plan.description">
                <div class="absolute left-full top-0 ml-2 hidden group-hover:block z-[9999]">
                    <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg px-4 py-3 shadow-xl border border-gray-600" style="min-width: 280px; max-width: 400px;">
                        <div class="font-semibold mb-1.5 text-lask-1-light flex items-center gap-1">📝 詳細</div>
                        <p class="text-gray-200 whitespace-pre-wrap leading-relaxed" x-text="plan.description"></p>
                        {{-- 三角形 --}}
                        <div class="absolute right-full top-3 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ステータスとアクション --}}
        @if($showActions)
        <div class="flex items-center gap-2 ml-3 flex-shrink-0">
            {{-- ステータスバッジ --}}
            <span 
                class="text-xs px-2 py-1 rounded-full font-medium transition-colors"
                :class="{
                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300': plan.status === 'planned',
                    'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300': plan.status === 'in_progress',
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300': plan.status === 'completed',
                    'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300': plan.status === 'skipped'
                }"
            >
                <span x-text="{ planned: '予定', in_progress: '進行中', completed: '完了', skipped: 'スキップ' }[plan.status]"></span>
            </span>

            {{-- クイックアクションボタン --}}
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                {{-- 開始/ポモドーロ --}}
                <template x-if="plan.status === 'planned'">
                    <button 
                        @click="startPomodoro()"
                        class="p-1.5 rounded-lg bg-sky-100 text-sky-600 hover:bg-sky-200 dark:bg-sky-900/50 dark:text-sky-400 dark:hover:bg-sky-900 transition"
                        title="開始（ポモドーロ）"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </template>

                {{-- 完了 --}}
                <template x-if="plan.status !== 'completed' && plan.status !== 'skipped'">
                    <button 
                        @click="updateStatus('completed')"
                        class="p-1.5 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 dark:bg-emerald-900/50 dark:text-emerald-400 dark:hover:bg-emerald-900 transition"
                        title="完了"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>

                {{-- スキップ --}}
                <template x-if="plan.status !== 'completed' && plan.status !== 'skipped'">
                    <button 
                        @click="updateStatus('skipped')"
                        class="p-1.5 rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 dark:bg-rose-900/50 dark:text-rose-400 dark:hover:bg-rose-900 transition"
                        title="スキップ"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>

                {{-- 戻す（完了/スキップ時） --}}
                <template x-if="plan.status === 'completed' || plan.status === 'skipped'">
                    <button 
                        @click="updateStatus('planned')"
                        class="p-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 transition"
                        title="予定に戻す"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </button>
                </template>

                {{-- メニュー --}}
                <div class="relative" @click.away="showMenu = false">
                    <button 
                        @click="showMenu = !showMenu"
                        class="p-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    
                    {{-- ドロップダウンメニュー --}}
                    <div 
                        x-show="showMenu"
                        x-transition
                        class="absolute right-0 mt-1 w-36 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-20"
                    >
                        <button @click="edit()" class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            編集
                        </button>
                        <button @click="remove()" class="w-full px-3 py-2 text-left text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            削除
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
