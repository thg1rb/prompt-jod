<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="$store.theme.init()">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Theme script - runs immediately to prevent flash -->
        <script>
            (function() {
                var theme = localStorage.getItem('theme');
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                if (!localStorage.getItem('theme')) {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                        if (e.matches) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    });
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-6">
            <!-- Logo -->
            <div class="mb-8">
                <a href="/">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-primary text-primary-foreground grid place-items-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight">PromptJod</span>
                    </div>
                </a>
            </div>

            <div class="w-full max-w-[400px] bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>