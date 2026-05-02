<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="$store.theme.init()">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
        <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6">
            <div class="mb-8 sm:mb-10">
                <a href="/">
                    <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 text-primary" />
                </a>
            </div>

            <div class="w-full max-w-md bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
