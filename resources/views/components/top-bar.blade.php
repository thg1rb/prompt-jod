<header
    class="sticky top-0 z-30 h-16 bg-background/80 backdrop-blur-md border-b border-border flex items-center justify-between px-4 lg:hidden"
    x-data
>
    <!-- Logo -->
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="h-8 w-8 rounded-lg bg-primary text-primary-foreground grid place-items-center shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </a>
        <a href="{{ route('dashboard') }}" class="font-semibold text-foreground shrink-0">PromptJod</a>
    </div>

    <!-- Theme toggle & user profile -->
    <div class="flex items-center gap-3">
        <x-theme-toggle size="sm" />

        <!-- User profile -->
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 hover:bg-muted rounded-lg px-2 py-1.5 transition-colors">
            <div class="h-8 w-8 rounded-full bg-primary/10 text-primary grid place-items-center font-semibold shrink-0">
                {{ Auth::user()->name[0] ?? '?' }}
            </div>
            <div class="hidden sm:block min-w-0">
                <div class="text-sm font-medium text-foreground truncate max-w-[100px]">{{ Auth::user()->name }}</div>
                <div class="text-xs text-muted-foreground truncate max-w-[100px]">{{ Auth::user()->email }}</div>
            </div>
        </a>
    </div>
</header>
