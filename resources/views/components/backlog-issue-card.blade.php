@props(['issue'])

@php
    $priorityColors = [
        '高' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
        '中' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
        '低' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
    ];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition">
    {{-- ヘッダー --}}
    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <span class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ $issue['issueKey'] }}</span>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $priorityColors[$issue['priority']['name']] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $issue['priority']['name'] }}
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                style="background-color: {{ $issue['status']['color'] }}20; color: {{ $issue['status']['color'] }}">
                {{ $issue['status']['name'] }}
            </span>
        </div>
    </div>

    {{-- 本体 --}}
    <div class="p-4">
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $issue['summary'] }}</h4>
        
        @if (!empty($issue['description']))
            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                {{ Str::limit($issue['description'], 100) }}
            </p>
        @endif

        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
            @if ($issue['dueDate'] ?? null)
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ $issue['dueDate'] }}
                </span>
            @endif
            @if ($issue['estimatedHours'] ?? null)
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $issue['estimatedHours'] }}h
                </span>
            @endif
            @if (!empty($issue['milestone']))
                <span class="flex items-center gap-1">
                    <x-icon name="flag" class="w-3.5 h-3.5" />
                    {{ $issue['milestone'][0]['name'] }}
                </span>
            @endif
        </div>
    </div>
</div>
