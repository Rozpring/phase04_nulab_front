{{-- 
    テーマ切り替えコンポーネント
    -------------------------------------------
    - ライト/ダーク/システムモード切り替え
    - 5つのカラーテーマ選択（Lavender/Mint/Peach/Sky/Rose）
--}}
@props(['inline' => false])

<div 
    x-data="{
        mode: localStorage.getItem('theme') || 'light',
        colorTheme: localStorage.getItem('colorTheme') || 'sky',
        showMenu: false,
        
        themes: [
            { id: 'lavender', label: 'Lavender', color: '#8b7db3' },
            { id: 'mint', label: 'Mint', color: '#6b9080' },
            { id: 'peach', label: 'Peach', color: '#b38b7d' },
            { id: 'sky', label: 'Sky', color: '#6b8cae' },
            { id: 'rose', label: 'Rose', color: '#b37d8b' }
        ],
        
        init() {
            this.applyTheme();
        },
        
        toggleMode() {
            const modes = ['light', 'dark', 'system'];
            const idx = modes.indexOf(this.mode);
            this.mode = modes[(idx + 1) % modes.length];
            this.applyMode();
        },
        
        setMode(newMode) {
            this.mode = newMode;
            this.applyMode();
        },
        
        setColorTheme(themeId) {
            this.colorTheme = themeId;
            localStorage.setItem('colorTheme', themeId);
            document.documentElement.setAttribute('data-theme', themeId);
            this.showMenu = false;
        },
        
        applyMode() {
            localStorage.setItem('theme', this.mode);
            const isDark = this.mode === 'dark' || 
                (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
        
        applyTheme() {
            this.applyMode();
            document.documentElement.setAttribute('data-theme', this.colorTheme);
        },
        
        get currentTheme() {
            return this.themes.find(t => t.id === this.colorTheme) || this.themes[3];
        }
    }"
    x-init="init()"
    class="relative"
    {{ $attributes }}
>
    {{-- トリガーボタン --}}
    <button
        @click="showMenu = !showMenu"
        class="flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200"
        :class="showMenu 
            ? 'bg-white dark:bg-gray-700 shadow-md' 
            : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
    >
        {{-- カラーインジケーター --}}
        <span 
            class="w-4 h-4 rounded-full ring-2 ring-white dark:ring-gray-700 shadow-sm transition-colors"
            :style="`background-color: ${currentTheme.color}`"
        ></span>
        
        {{-- モードアイコン --}}
        <template x-if="mode === 'light'">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
            </svg>
        </template>
        <template x-if="mode === 'dark'">
            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
            </svg>
        </template>
        <template x-if="mode === 'system'">
            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/>
            </svg>
        </template>
        
        {{-- 矢印 --}}
        <svg class="w-3 h-3 text-gray-400 transition-transform" :class="showMenu && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    {{-- ドロップダウンメニュー --}}
    <div 
        x-show="showMenu"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="showMenu = false"
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-2 z-50"
    >
        {{-- モード切替セクション --}}
        <div class="mb-2 pb-2 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 px-2 mb-1.5 font-medium">表示モード</p>
            <div class="flex gap-1">
                <button
                    @click="setMode('light')"
                    class="flex-1 px-2 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1"
                    :class="mode === 'light' 
                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' 
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                    </svg>
                    ライト
                </button>
                <button
                    @click="setMode('dark')"
                    class="flex-1 px-2 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1"
                    :class="mode === 'dark' 
                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' 
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                    </svg>
                    ダーク
                </button>
                <button
                    @click="setMode('system')"
                    class="flex-1 px-2 py-1.5 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1"
                    :class="mode === 'system' 
                        ? 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200' 
                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/>
                    </svg>
                    自動
                </button>
            </div>
        </div>
        
        {{-- カラーテーマセクション --}}
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 px-2 mb-1.5 font-medium">テーマカラー</p>
            <div class="space-y-0.5">
                <template x-for="theme in themes" :key="theme.id">
                    <button
                        @click="setColorTheme(theme.id)"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition"
                        :class="colorTheme === theme.id 
                            ? 'bg-gray-100 dark:bg-gray-700' 
                            : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    >
                        {{-- カラードット --}}
                        <span 
                            class="w-5 h-5 rounded-full shadow-sm ring-1 ring-gray-200 dark:ring-gray-600"
                            :style="`background-color: ${theme.color}`"
                        ></span>
                        
                        {{-- ラベル --}}
                        <span 
                            class="flex-1 text-left"
                            :class="colorTheme === theme.id 
                                ? 'text-gray-900 dark:text-white font-medium' 
                                : 'text-gray-600 dark:text-gray-300'"
                            x-text="theme.label"
                        ></span>
                        
                        {{-- チェックマーク --}}
                        <svg 
                            x-show="colorTheme === theme.id" 
                            class="w-4 h-4 text-green-500" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke-width="2" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
