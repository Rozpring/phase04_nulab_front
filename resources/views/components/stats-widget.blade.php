@props(['label', 'value', 'color' => 'gray', 'icon' => 'chart-bar'])

@php
    $colorClasses = [
        'gray' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        'slate' => 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200',
        'sky' => 'bg-sky-100 dark:bg-sky-900/50 text-sky-800 dark:text-sky-200',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200',
        'rose' => 'bg-rose-100 dark:bg-rose-900/50 text-rose-800 dark:text-rose-200',
        'amber' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200',
        'indigo' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200',
    ];
@endphp

<div class="rounded-xl p-4 {{ $colorClasses[$color] ?? $colorClasses['gray'] }} transition-transform hover:scale-105">
    <div class="flex items-center gap-3">
        <x-icon :name="$icon" class="w-6 h-6" />
        <div>
            <div class="text-2xl font-bold">{{ $value }}</div>
            <div class="text-sm opacity-75">{{ $label }}</div>
        </div>
    </div>
</div>

