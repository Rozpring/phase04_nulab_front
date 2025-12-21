<nav x-data="{ open: false }" class="bg-base border-b border-default backdrop-blur-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.*')">
                        <x-icon name="calendar" class="w-4 h-4 mr-1 inline" />
                        {{ __('計画') }}
                    </x-nav-link>
                    <x-nav-link :href="route('backlog.settings')" :active="request()->routeIs('backlog.*')">
                        <x-icon name="link" class="w-4 h-4 mr-1 inline" />
                        {{ __('Backlog連携') }}
                    </x-nav-link>
                    <x-nav-link :href="route('analysis.index')" :active="request()->routeIs('analysis.*')">
                        <x-icon name="cpu-chip" class="w-4 h-4 mr-1 inline" />
                        {{ __('AI分析') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                {{-- 新規計画ボタン --}}
                <button 
                    onclick="Alpine.store('ui').openModal('planCreate')"
                    class="p-2 text-accent hover:bg-muted rounded-lg transition-smooth"
                    title="新規計画 (⌘N)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                
                {{-- ヘルプボタン（自己完結型） --}}
                <div x-data="{ helpOpen: false }" class="relative">
                    <button 
                        @click="helpOpen = true"
                        class="p-2 text-secondary hover:text-primary hover:bg-muted rounded-lg transition-smooth"
                        title="ヘルプ (H)"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    
                    {{-- ヘルプモーダル --}}
                    <template x-teleport="body">
                        <div 
                            x-show="helpOpen"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            @keydown.escape.window="helpOpen = false"
                            style="display: none;"
                        >
                            {{-- オーバーレイ --}}
                            <div class="absolute inset-0 bg-black/50" @click="helpOpen = false"></div>
                            
                            {{-- モーダルコンテンツ --}}
                            <div 
                                class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                @click.stop
                            >
                                {{-- ヘッダー --}}
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-lask-dark rounded-t-2xl">
                                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                        <x-icon name="book-open" class="w-6 h-6" />
                                        LASKの使い方
                                    </h2>
                                    <button @click="helpOpen = false" class="p-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                {{-- コンテンツ --}}
                                <div class="p-6 space-y-6">
                                    <div class="text-center pb-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">LASKへようこそ！</h3>
                                        <p class="text-gray-600 dark:text-gray-400">BacklogとAIで効率的な学習計画を自動生成</p>
                                    </div>
                                    
                                    <div class="grid gap-4">
                                        <div class="flex gap-4 p-4 bg-lask-accent-subtle rounded-xl">
                                            <x-icon name="inbox-arrow-down" class="w-8 h-8 text-lask-1 flex-shrink-0" />
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">1. Backlogから課題をインポート</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Backlog設定でAPIキーを登録し、課題を取り込みます。</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-4 p-4 bg-[#6b8cae]/20 rounded-xl">
                                            <x-icon name="cpu-chip" class="w-8 h-8 text-[#6b8cae] flex-shrink-0" />
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">2. AI計画生成</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">「計画を生成」で最適なスケジュールを自動生成。</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-4 p-4 bg-[#2c3e50]/15 rounded-xl">
                                            <x-icon name="clipboard-document-list" class="w-8 h-8 text-[#2c3e50] dark:text-gray-300 flex-shrink-0" />
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">3. 計画を管理</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">開始・完了・スキップの操作が可能。</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex gap-4 p-4 bg-[#8fbc8f]/25 rounded-xl">
                                            <x-icon name="clock" class="w-8 h-8 text-[#8fbc8f] flex-shrink-0" />
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">4. ポモドーロタイマー</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">25分作業 + 5分休憩で集中力を維持。</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- フッター --}}
                                <div class="flex items-center justify-center px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
                                    <button 
                                        @click="helpOpen = false"
                                        class="px-6 py-2 bg-lask-dark text-white rounded-lg hover:bg-lask-dark/80 transition font-medium flex items-center gap-2"
                                    >
                                        <x-icon name="rocket-launch" class="w-4 h-4" />
                                        始める
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                {{-- 通知設定 --}}
                <x-notification-settings />
                
                {{-- テーマ切り替え --}}
                <x-theme-toggle />
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.*')">
                <x-icon name="calendar" class="w-4 h-4 mr-1 inline" />
                {{ __('計画') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('backlog.settings')" :active="request()->routeIs('backlog.*')">
                <x-icon name="link" class="w-4 h-4 mr-1 inline" />
                {{ __('Backlog連携') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('analysis.index')" :active="request()->routeIs('analysis.*')">
                <x-icon name="cpu-chip" class="w-4 h-4 mr-1 inline" />
                {{ __('AI分析') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
