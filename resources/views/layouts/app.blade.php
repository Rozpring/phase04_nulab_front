<!DOCTYPE html>
<html 
    lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
    class="scroll-smooth"
    x-data
    :class="{ 'dark': localStorage.getItem('theme') === 'dark' || (localStorage.getItem('theme') === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
    :data-theme="localStorage.getItem('colorTheme') || 'sky'"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'NextLog') }}</title>

        {{-- テーマ初期化（ちらつき防止） --}}
        <script>
            (function() {
                const theme = localStorage.getItem('theme') || 'light';
                const colorTheme = localStorage.getItem('colorTheme') || 'sky';
                
                // ダークモード適用
                const isDark = theme === 'dark' || 
                    (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                
                // カラーテーマ適用
                document.documentElement.setAttribute('data-theme', colorTheme);
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-subtle text-primary">
        <div class="min-h-screen transition-colors duration-300">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-900 border-b border-default shadow-sm">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        {{-- グローバルモーダル --}}
        <x-plan-edit-modal />
        <x-plan-create-form />
        <x-issue-detail-modal />
        <x-keyboard-shortcuts />
        <x-help-guide />
        
        {{-- トースト通知 --}}
        <x-toast-notifications />
    </body>
</html>
