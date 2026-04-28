<header
    class="sticky top-0 z-30 h-16 bg-background/80 backdrop-blur-md border-b border-border flex items-center px-4 gap-3 lg:hidden"
    x-data
>
    <!-- Logo -->
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="h-8 w-8 rounded-lg bg-primary text-primary-foreground grid place-items-center">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </a>
        <a href="{{ route('dashboard') }}" class="font-semibold text-text-primary">PromptJod</a>
    </div>

    <!-- Spacer -->
    <div class="flex-1" />

    <!-- Theme toggle & user profile -->
    <div class="flex items-center gap-2">
        <x-theme-toggle size="sm" />

        <!-- User profile button -->
        <a
            href="{{ route('profile.edit') }}"
            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary-muted text-primary hover:bg-primary/20 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            aria-label="โปรไฟล์"
        >
            <span class="text-sm font-semibold">{{ Auth::user()->name[0] ?? '?' }}</span>
        </a>
    </div>
</header>
