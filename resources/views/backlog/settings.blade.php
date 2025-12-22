<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Backlog連携設定') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            {{-- フラッシュメッセージ --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-100 dark:bg-emerald-900/50 border border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-300 rounded-lg flex items-center gap-2">
                    <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0" />
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl">
                {{-- ヘッダー --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Backlog API設定</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">あなたのBacklogアカウントと連携します</p>
                    </div>
                    @if ($setting->is_connected)
                        <span class="ml-auto inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full text-sm">
                            <x-icon name="check-circle" class="w-4 h-4" />
                            接続済み
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('backlog.settings') }}" class="p-6 space-y-6">
                    @csrf

                    {{-- スペースURL --}}
                    <div>
                        <x-input-label for="space_url" value="BacklogスペースURL" />
                        <div class="mt-1 flex rounded-lg shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm">
                                https://
                            </span>
                            <input 
                                type="text" 
                                id="space_url" 
                                name="space_url" 
                                value="{{ old('space_url', $setting->space_url ? str_replace('https://', '', $setting->space_url) : '') }}"
                                class="flex-1 block w-full rounded-none rounded-r-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="your-space.backlog.com"
                                required
                            >
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            例: your-space.backlog.com または your-space.backlog.jp
                        </p>
                        <x-input-error :messages="$errors->get('space_url')" class="mt-2" />
                    </div>

                    {{-- APIキー --}}
                    <div>
                        <x-input-label for="api_key" value="APIキー" />
                        <div class="mt-1 relative">
                            <input 
                                type="password" 
                                id="api_key" 
                                name="api_key" 
                                value="{{ old('api_key', $setting->api_key) }}"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 pr-10"
                                placeholder="APIキーを入力"
                                required
                            >
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            <a href="https://support-ja.backlog.com/hc/ja/articles/360035641754" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                APIキーの取得方法 →
                            </a>
                        </p>
                        <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
                    </div>

                    {{-- プロジェクト選択 --}}
                    <div>
                        <x-input-label for="selected_project_id" value="連携するプロジェクト" />
                        <select 
                            id="selected_project_id" 
                            name="selected_project_id" 
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">プロジェクトを選択...</option>
                            @foreach ($projects as $project)
                                <option 
                                    value="{{ $project['id'] }}" 
                                    data-name="{{ $project['name'] }}"
                                    {{ $setting->selected_project_id == $project['id'] ? 'selected' : '' }}
                                >
                                    [{{ $project['projectKey'] }}] {{ $project['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="selected_project_name" id="selected_project_name" value="{{ $setting->selected_project_name }}">
                        <x-input-error :messages="$errors->get('selected_project_id')" class="mt-2" />
                    </div>

                    {{-- 接続情報 --}}
                    @if ($setting->last_synced_at)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p>最終同期: {{ $setting->last_synced_at->format('Y年m月d日 H:i') }}</p>
                                @if ($setting->selected_project_name)
                                    <p>選択中のプロジェクト: {{ $setting->selected_project_name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ボタン --}}
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                            <button type="button" 
                                x-data="{ testing: false, result: null }"
                                @click="testing = true; result = null; 
                                    fetch('/backlog/test-connection', { 
                                        method: 'POST', 
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ space_url: document.getElementById('space_url').value, api_key: document.getElementById('api_key').value })
                                    })
                                    .then(r => r.json())
                                    .then(d => { result = d.success ? 'success' : 'error'; testing = false; })
                                    .catch(() => { result = 'error'; testing = false; })"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-center gap-2"
                                :disabled="testing"
                            >
                                <svg x-show="testing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-show="!testing && result === null" class="flex items-center gap-1">
                                    <x-icon name="link" class="w-4 h-4" />
                                    接続テスト
                                </span>
                                <span x-show="result === 'success'" class="text-emerald-600 flex items-center gap-1">
                                    <x-icon name="check-circle" class="w-4 h-4" />
                                    接続OK
                                </span>
                                <span x-show="result === 'error'" class="text-rose-600 flex items-center gap-1">
                                    <x-icon name="x-circle" class="w-4 h-4" />
                                    接続失敗
                                </span>
                            </button>
                            <x-primary-button class="w-full sm:w-auto justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                設定を保存
                            </x-primary-button>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('backlog.issues') }}" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium text-sm">
                                <x-icon name="arrow-right" class="w-4 h-4" />
                                課題をインポートに進む
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ヘルプ --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <x-icon name="book-open" class="w-5 h-5 text-indigo-500" />
                    セットアップガイド
                </h4>
                <ol class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">1</span>
                        <span>Backlogの個人設定からAPIキーを発行します</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">2</span>
                        <span>スペースURLとAPIキーを入力して保存します</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3</span>
                        <span>連携したいプロジェクトを選択します</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">4</span>
                        <span>「課題をインポート」から学習計画に追加したい課題を選択します</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('api_key');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('selected_project_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('selected_project_name').value = selectedOption.dataset.name || '';
        });
    </script>
</x-app-layout>
