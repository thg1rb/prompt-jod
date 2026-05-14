<header
    class="sticky top-0 z-30 h-[56px] bg-surface-elevated/80 backdrop-blur-ios border-b border-border flex items-center justify-between px-4 lg:hidden"
    x-data
>
    <!-- Logo -->
    <div class="flex items-center gap-2.5">
        <a href="{{ route('dashboard') }}" class="h-8 w-8 rounded-xl bg-primary text-primary-foreground grid place-items-center shrink-0 shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </a>
        <a href="{{ route('dashboard') }}" class="font-semibold text-foreground text-[15px] tracking-tight">PromptJod</a>
    </div>

    <!-- Theme toggle & user profile -->
    <div class="flex items-center gap-1">
        <x-theme-toggle size="sm" />

        <!-- User profile -->
        <a href="{{ route('profile.edit') }}" class="ml-1 flex items-center gap-2 hover:bg-surface-subtle rounded-xl px-2 py-1.5 transition-colors">
            <div class="h-7 w-7 rounded-full bg-primary/10 text-primary grid place-items-center font-semibold shrink-0 text-[13px] ring-1 ring-primary/20">
                {{ Auth::user()->name[0] ?? '?' }}
            </div>
            <div class="hidden sm:block min-w-0">
                <div class="text-[13px] font-medium text-foreground truncate max-w-[100px] leading-tight">{{ Auth::user()->name }}</div>
            </div>
        </a>
    </div>
</header>