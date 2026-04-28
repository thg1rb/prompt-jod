<aside
    class="hidden lg:flex flex-col border-r border-border bg-sidebar transition-all duration-300"
    :class="$store.sidebar.collapsed ? 'w-16' : 'w-64'"
    x-init="$store.sidebar.init()"
>
    <!-- Logo section -->
    <div class="h-16 flex items-center gap-2 border-b border-border overflow-hidden" :class="$store.sidebar.collapsed ? 'px-3 justify-center' : 'px-5'">
        <!-- App logo (hidden when collapsed) -->
        <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex items-center gap-2 shrink-0" style="display: none;">
            <a href="{{ route('dashboard') }}" class="h-9 w-9 rounded-lg bg-primary text-primary-foreground grid place-items-center">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </a>
            <div class="truncate">
                <div class="font-semibold text-text-primary leading-tight">PromptJod</div>
                <div class="text-xs text-text-muted">จัดการสลิปอัจฉริยะ</div>
            </div>
        </div>

        <!-- Collapse/Expand toggle button -->
        <button
            @click="$store.sidebar.toggle()"
            type="button"
            class="shrink-0 inline-flex items-center justify-center p-1.5 rounded-lg text-text-secondary hover:bg-sidebar-accent hover:text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            :class="$store.sidebar.collapsed ? 'mx-auto' : 'ml-auto'"
            :aria-label="$store.sidebar.collapsed ? 'ขยายเมนู' : 'ย่อเมนู'"
        >
            <svg x-show="!$store.sidebar.collapsed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg x-show="$store.sidebar.collapsed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Navigation links -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">กระเป๋าเงิน</span>
        </a>

        <!-- Profile -->
        <a
            href="{{ route('profile.edit') }}"
            class="{{ request()->routeIs('profile.*') ? '!bg-primary/10 !text-primary font-medium' : 'text-text-secondary hover:bg-sidebar-accent' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">โปรไฟล์</span>
        </a>
    </nav>

    <!-- User profile section -->
    <div class="p-3 border-t border-border">
        <div class="flex items-center gap-3 px-2 py-2" :class="$store.sidebar.collapsed ? 'justify-center' : ''">
            <div class="h-9 w-9 rounded-full bg-primary-muted text-primary grid place-items-center font-semibold shrink-0">
                {{ Auth::user()->name[0] ?? '?' }}
            </div>
            <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex-1 min-w-0">
                <div class="text-sm font-medium text-text-primary truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-text-muted truncate">{{ Auth::user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline" x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
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
