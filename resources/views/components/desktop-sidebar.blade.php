<aside class="hidden lg:flex flex-col w-64 shrink-0 border-r border-border bg-sidebar">
    <!-- Logo section -->
    <div class="h-16 flex items-center gap-2 px-5 border-b border-border">
        <a href="{{ route('dashboard') }}" class="h-9 w-9 rounded-lg bg-primary text-primary-foreground grid place-items-center">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </a>
        <div>
            <div class="font-semibold text-text-primary leading-tight">PromptJod</div>
            <div class="text-xs text-text-muted">จัดการสลิปอัจฉริยะ</div>
        </div>
    </div>

    <!-- Navigation links -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>บัญชีเงิน</span>
        </a>

        <!-- Profile -->
        <a
            href="{{ route('profile.edit') }}"
            class="{{ request()->routeIs('profile.*') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>โปรไฟล์</span>
        </a>
    </nav>

    <!-- User profile section -->
    <div class="p-3 border-t border-border">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="h-9 w-9 rounded-full bg-primary-muted text-primary grid place-items-center font-semibold">
                {{ Auth::user()->name[0] ?? '?' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-text-primary truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-text-muted truncate">{{ Auth::user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-text-secondary hover:bg-sidebar-accent hover:text-destructive transition-colors focus:outline-none focus:ring-2 focus:ring-destructive focus:ring-offset-2"
                    aria-label="ออกจากระบบ"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
