<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="$store.theme.init()" class="antialiased">
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
                // Check for saved theme or system preference
                var theme = localStorage.getItem('theme');
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }

                // Apply theme class immediately before any rendering
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Listen for system theme changes (if no manual preference)
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
    <body
        class="antialiased"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-100"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-out duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-100"
    >
        <!-- Desktop layout: sidebar + content side by side -->
        <div class="hidden lg:flex min-h-screen">
            <!-- Desktop sidebar -->
            <x-desktop-sidebar />

            <!-- Main content area -->
            <div class="flex-1 flex flex-col min-h-screen bg-background">
                <!-- Page Content -->
                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile/tablet layout: top bar + bottom bar -->
        <div class="lg:hidden flex flex-col min-h-screen bg-background">
            <!-- Top bar (mobile/tablet only) -->
            <x-top-bar />

            <!-- Main content area -->
            <div class="flex-1">
                <!-- Page Content -->
                <main class="px-4 py-6 sm:px-6 lg:px-8 pb-20">
                    {{ $slot }}
                </main>
            </div>

            <!-- Mobile bottom tab bar -->
            <x-mobile-tab-bar />
        </div>
    </body>
</html>
