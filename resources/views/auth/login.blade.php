<x-guest-layout>
    {{-- ログインカード --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        {{-- ロゴ --}}
        <div class="flex items-center justify-center gap-2 mb-8">
            <svg class="w-8 h-8 text-lask-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
            <span class="text-2xl font-bold text-gray-800">NextLog</span>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" name="login-form" id="login-form" autocomplete="on">
            @csrf

            {{-- メールアドレス --}}
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-600 mb-2">
                    メールアドレス
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="email"
                        placeholder="メールアドレス"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-lask-accent focus:border-transparent transition"
                    >
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- パスワード --}}
            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-gray-600 mb-2">
                    パスワード
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="パスワード"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-lask-accent focus:border-transparent transition"
                    >
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- ログイン状態を保持 --}}
            <div class="flex items-center mb-6">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember"
                    class="w-4 h-4 text-lask-accent bg-white border-gray-300 rounded focus:ring-lask-accent focus:ring-2"
                >
                <label for="remember_me" class="ml-2 text-sm text-gray-600">
                    ログイン状態を保持
                </label>
            </div>

            {{-- ログインボタン --}}
            <button 
                type="submit"
                class="w-full py-3 px-4 bg-lask-accent text-white font-semibold rounded-xl hover:bg-lask-accent-hover focus:outline-none focus:ring-2 focus:ring-lask-accent focus:ring-offset-2 transition"
            >
                ログイン
            </button>

            {{-- リンク --}}
            <div class="mt-6 text-center space-y-3">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="block text-sm text-lask-accent hover:underline">
                        パスワードをお忘れですか？
                    </a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block text-sm text-lask-accent hover:underline font-medium">
                        アカウント登録
                    </a>
                @endif
            </div>
        </form>
    </div>
</x-guest-layout>
