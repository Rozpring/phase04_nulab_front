@props(['advice'])

@php
    $typeClasses = [
        'positive' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
        'neutral' => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700',
        'action' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800',
        'insight' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
        'tip' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800',
        'info' => 'bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800',
    ];
    
    $iconColors = [
        'positive' => 'text-emerald-500',
        'neutral' => 'text-gray-500',
        'action' => 'text-indigo-500',
        'insight' => 'text-purple-500',
        'tip' => 'text-amber-500',
        'info' => 'text-sky-500',
    ];
    
    // 重要度バッジ
    $priorityBadges = [
        'urgent' => ['label' => '緊急', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300'],
        'recommended' => ['label' => '推奨', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'],
        'reference' => ['label' => '参考', 'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'],
    ];
    $priority = $advice['priority'] ?? 'reference';
    $badge = $priorityBadges[$priority] ?? $priorityBadges['reference'];
    $iconName = $advice['icon'] ?? 'light-bulb';
    $iconColor = $iconColors[$advice['type']] ?? 'text-gray-500';
@endphp

<div class="rounded-xl p-4 border {{ $typeClasses[$advice['type']] ?? $typeClasses['neutral'] }} transition-all hover:shadow-md hover:-translate-y-0.5">
    <div class="flex items-start gap-3">
        <x-icon :name="$iconName" class="w-6 h-6 flex-shrink-0 {{ $iconColor }}" />
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate">
                    {{ $advice['title'] }}
                </h4>
                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $badge['class'] }} flex-shrink-0">
                    {{ $badge['label'] }}
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $advice['content'] }}
            </p>
        </div>
    </div>
</div>

