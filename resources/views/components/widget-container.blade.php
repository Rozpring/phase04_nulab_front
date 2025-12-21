{{-- 
    折りたたみ可能なウィジェットコンテナ
    表示/非表示と折りたたみ状態をlocalStorageに保存
--}}
@props(['id', 'title', 'icon' => '📦'])

<div 
    x-data="{
        get visible() { return $store.widgets.state['{{ $id }}']?.visible ?? true },
        get collapsed() { return $store.widgets.state['{{ $id }}']?.collapsed ?? false },
        toggleCollapse() { $store.widgets.toggle('{{ $id }}', 'collapsed') },
        toggleVisible() { $store.widgets.toggle('{{ $id }}', 'visible') }
    }"
    x-show="visible"
    x-transition
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden']) }}
>
    {{-- ヘッダー（クリックで折りたたみ） --}}
    <div 
        class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
        @click="toggleCollapse()"
    >
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
            <span class="text-xl mr-2">{{ $icon }}</span>
            {{ $title }}
        </h3>
        <div class="flex items-center gap-2">
            {{-- 非表示ボタン --}}
            <button 
                @click.stop="toggleVisible()"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                title="ウィジェットを非表示"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
            {{-- 折りたたみアイコン --}}
            <svg 
                class="w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="collapsed ? '' : 'rotate-180'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
    
    {{-- コンテンツ（折りたたみ対応） --}}
    <div 
        x-show="!collapsed"
        x-collapse
    >
        {{ $slot }}
    </div>
</div>
