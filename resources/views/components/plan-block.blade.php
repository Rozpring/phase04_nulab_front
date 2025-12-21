@props(['plan'])

@php
    // テーマ連動カラー - 補色パレット使用
    $typeColors = [
        'study' => 'bg-lask-accent-subtle',       // 学習: テーマアクセント
        'work' => 'bg-[var(--color-1)]/20',       // 作業: 補色1（濃い色）
        'break' => 'bg-[var(--color-4)]/30',      // 休憩: 補色4（ベージュ/グレー系）
        'review' => 'bg-[var(--color-3)]/25',     // 復習: 補色3（くすみ色）
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
    $iconName = $typeIcons[$plan->plan_type] ?? 'clipboard-document-list';
    $iconColor = $typeIconColors[$plan->plan_type] ?? 'text-lask-text-secondary';
@endphp

<div class="px-5 py-4 rounded-2xl {{ $typeColors[$plan->plan_type] ?? 'bg-gray-100 dark:bg-gray-700' }} hover:shadow-md transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{-- 時間とタイプ --}}
            <div class="flex items-center gap-2 mb-1.5">
                <x-icon :name="$iconName" class="w-5 h-5 {{ $iconColor }}" />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ $plan->time_range }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                    {{ $plan->duration_display }}
                </span>
                @if ($plan->importedIssue)
                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">
                        {{ $plan->importedIssue->issue_key }}
                    </span>
                @endif
            </div>
            
            {{-- タイトル --}}
            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->title }}</h4>
            
            {{-- AI理由 --}}
            @if ($plan->ai_reason)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-start gap-1.5 leading-relaxed">
                    <x-icon name="light-bulb" class="w-3.5 h-3.5 text-lask-1 flex-shrink-0 mt-0.5" />
                    <span class="line-clamp-2">{{ $plan->ai_reason }}</span>
                </p>
            @endif
        </div>

        {{-- ステータス --}}
        <div class="flex items-center gap-2 ml-3">
            <span class="text-xs font-medium {{ $statusColors[$plan->status] ?? 'text-gray-500' }}">
                {{ $plan->status_info['label'] }}
            </span>
        </div>
    </div>
</div>
