<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="sky">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'NextLog') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8" style="background: linear-gradient(135deg, #e8f0f7 0%, #f0f5fa 50%, #d8e6f5 100%);">
            {{ $slot }}
            
            {{-- フッター --}}
            <p class="mt-8 text-sm text-gray-400">
                NextLog - タスク管理をシンプルに
            </p>
        </div>
    </body>
</html>
