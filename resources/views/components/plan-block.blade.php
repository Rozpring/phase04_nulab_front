@props(['plan'])

@php
    // タイプ別背景色 - 統一感のある薄い背景色
    // 全タイプで同程度の薄さを維持し、視覚的な一貫性を確保
    $typeColors = [
        'study' => 'bg-gray-100/80 dark:bg-gray-700/50',       // 学習: 薄いグレー
        'work' => 'bg-gray-100/80 dark:bg-gray-700/50',        // 作業: 薄いグレー
        'break' => 'bg-gray-100/60 dark:bg-gray-700/40',       // 休憩: さらに薄いグレー
        'review' => 'bg-gray-100/80 dark:bg-gray-700/50',      // 復習: 薄いグレー
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
    $statusColors = [
        'planned' => 'text-lask-text-secondary',
        'in_progress' => 'text-lask-1',
        'completed' => 'text-lask-success',
        'skipped' => 'text-lask-warning',
    ];
    
    // ステータスに基づくカードスタイル
    $statusCardStyles = [
        'planned' => '',
        'in_progress' => 'border-l-4 border-l-lask-accent ring-1 ring-lask-accent/30 shadow-sm',
        'completed' => 'opacity-60',
        'skipped' => 'opacity-50 bg-gray-100 dark:bg-gray-700/50',
    ];
    
    // 現在時刻との比較（完了・スキップ済みでない場合のみ）
    $now = now();
    $isCurrentTask = false;
    $isUpcoming = false;
    $minutesUntilStart = null;
    
    if (!in_array($plan->status, ['completed', 'skipped']) && $plan->scheduled_date->isToday()) {
        $startTime = $plan->scheduled_time;
        $endTime = $plan->end_time;
        
        if ($startTime && $endTime) {
            // 現在実行中のタスク（現在時刻が開始〜終了の間）
            if ($now->between($startTime, $endTime)) {
                $isCurrentTask = true;
            }
            // まもなく開始（30分以内）
            elseif ($now->lt($startTime)) {
                $minutesUntilStart = (int) $now->diffInMinutes($startTime);
                if ($minutesUntilStart <= 30) {
                    $isUpcoming = true;
                }
            }
        }
    }
    
    // 時間ベースのスタイル
    $timeBasedStyle = '';
    if ($isCurrentTask) {
        $timeBasedStyle = 'border-l-4 border-l-amber-500 ring-2 ring-amber-400/40 bg-amber-50/50 dark:bg-amber-900/20';
    } elseif ($isUpcoming) {
        $timeBasedStyle = 'border-l-2 border-l-yellow-400';
    }
    
    $iconName = $typeIcons[$plan->plan_type] ?? 'clipboard-document-list';
    $iconColor = $typeIconColors[$plan->plan_type] ?? 'text-lask-text-secondary';
    $cardStatusStyle = $statusCardStyles[$plan->status] ?? '';
    
    // 時間ベースのスタイルがある場合は、ステータススタイルより優先
    $finalCardStyle = $isCurrentTask || $isUpcoming ? $timeBasedStyle : $cardStatusStyle;
@endphp

<div class="px-5 py-4 rounded-2xl {{ $typeColors[$plan->plan_type] ?? 'bg-gray-100 dark:bg-gray-700' }} {{ $finalCardStyle }} hover:shadow-md transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{-- 時間とタイプ --}}
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <x-icon :name="$iconName" class="w-5 h-5 {{ $iconColor }}" />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ $plan->time_range }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                    {{ $plan->duration_display }}
                </span>
                @if ($isCurrentTask)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500 text-white font-bold animate-pulse">
                        NOW
                    </span>
                @elseif ($isUpcoming)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-400 text-yellow-900 font-medium">
                        {{ $minutesUntilStart }}分後
                    </span>
                @endif
                @if ($plan->importedIssue)
                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">
                        {{ $plan->importedIssue->issue_key }}
                    </span>
                @endif
            </div>
            
            {{-- タイトル --}}
            <h4 class="font-semibold {{ $plan->status === 'completed' ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $plan->title }}</h4>
            
            {{-- AI理由 --}}
            @if ($plan->ai_reason)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-start gap-1.5 leading-relaxed">
                    <x-icon name="light-bulb" class="w-3.5 h-3.5 text-lask-1 flex-shrink-0 mt-0.5" />
                    <span>{{ $plan->ai_reason }}</span>
                </p>
            @endif
        </div>

        {{-- ステータス --}}
        <div class="flex items-center gap-2 ml-3">
            @if ($plan->status === 'completed')
                <div class="w-5 h-5 rounded-full bg-lask-success/20 flex items-center justify-center">
                    <x-icon name="check" class="w-3.5 h-3.5 text-lask-success" />
                </div>
            @endif
            <span class="text-xs font-medium {{ $statusColors[$plan->status] ?? 'text-gray-500' }}">
                {{ $plan->status_info['label'] }}
            </span>
        </div>
    </div>
</div>
