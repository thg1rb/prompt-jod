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
            <div class="relative">
                <div class="h-7 w-7 rounded-full bg-primary/10 text-primary grid place-items-center font-semibold shrink-0 text-[13px] {{ Auth::user()->isPremium() ? 'ring-2 ring-primary' : 'ring-1 ring-primary/20' }}">
                    {{ Auth::user()->name[0] ?? '?' }}
                </div>
                @if(Auth::user()->isPremium())
                    <svg class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-[60%] h-3.5 w-3.5 text-primary drop-shadow-sm" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd"/></svg>
                @endif
            </div>
            <div class="hidden sm:block min-w-0">
                <div class="text-[13px] font-medium text-foreground truncate max-w-[100px] leading-tight">{{ Auth::user()->name }}</div>
            </div>
        </a>
    </div>
</header>