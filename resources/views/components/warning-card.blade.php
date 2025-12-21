@props(['pattern'])

@php
    $severityClasses = [
        'critical' => 'border-l-rose-500 bg-rose-50 dark:bg-rose-900/20',
        'warning' => 'border-l-amber-500 bg-amber-50 dark:bg-amber-900/20',
        'info' => 'border-l-sky-500 bg-sky-50 dark:bg-sky-900/20',
    ];
    
    $severityTextClasses = [
        'critical' => 'text-rose-800 dark:text-rose-200',
        'warning' => 'text-amber-800 dark:text-amber-200',
        'info' => 'text-sky-800 dark:text-sky-200',
    ];
    
    $severityIcons = [
        'critical' => 'exclamation-triangle',
        'warning' => 'exclamation-triangle',
        'info' => 'information-circle',
    ];
    
    $severityIconColors = [
        'critical' => 'text-rose-500',
        'warning' => 'text-amber-500',
        'info' => 'text-sky-500',
    ];
    
    $iconName = $severityIcons[$pattern['severity']] ?? 'exclamation-triangle';
    $iconColor = $severityIconColors[$pattern['severity']] ?? 'text-amber-500';
@endphp

<div class="border-l-4 rounded-r-lg p-4 {{ $severityClasses[$pattern['severity']] ?? $severityClasses['info'] }}">
    <div class="flex items-start gap-3">
        <x-icon :name="$iconName" class="w-6 h-6 flex-shrink-0 {{ $iconColor }}" />
        <div class="flex-1">
            <h4 class="font-semibold {{ $severityTextClasses[$pattern['severity']] ?? $severityTextClasses['info'] }}">
                {{ $pattern['title'] }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $pattern['message'] }}
            </p>
            @if (isset($pattern['frequency']) && $pattern['frequency'] > 0)
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        {{ $pattern['frequency'] }}回検出
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
