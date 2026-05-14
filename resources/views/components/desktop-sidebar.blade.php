<aside
    class="hidden lg:flex flex-col h-full border-r border-sidebar-border bg-sidebar transition-all duration-300 overflow-hidden"
    :class="$store.sidebar.collapsed ? 'w-[70px]' : 'w-[260px]'"
    x-init="$store.sidebar.init()"
>
    <!-- Logo section -->
    <div class="h-[60px] flex items-center border-b border-sidebar-border overflow-hidden shrink-0" :class="$store.sidebar.collapsed ? 'px-3 justify-center' : 'px-4'">
        <!-- App logo (hidden when collapsed) -->
        <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-2" class="flex items-center gap-3 w-full" style="display: none;">
            <a href="{{ route('dashboard') }}" class="h-9 w-9 rounded-xl bg-primary text-primary-foreground grid place-items-center shrink-0 shadow-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </a>
            <div class="min-w-0">
                <div class="font-semibold text-foreground text-[15px] tracking-tight">PromptJod</div>
                <div class="text-[11px] text-text-muted leading-tight">จัดการสลิปอัจฉริยะ</div>
            </div>
        </div>

        <!-- Collapse/Expand toggle button -->
        <button
            @click="$store.sidebar.toggle()"
            type="button"
            class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg text-sidebar-foreground hover:bg-surface-subtle active:bg-surface-elevated transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
            :class="$store.sidebar.collapsed ? 'mx-auto' : 'ml-auto'"
            :aria-label="$store.sidebar.collapsed ? 'ขยายเมนู' : 'ย่อเมนู'"
        >
            <svg x-show="!$store.sidebar.collapsed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-75" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg x-show="$store.sidebar.collapsed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-75" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Navigation links -->
    <nav class="flex-1 px-2.5 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary' : 'text-sidebar-foreground hover:bg-surface-subtle active:bg-surface-elevated' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="font-medium text-[14px]">หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? 'bg-primary/10 text-primary' : 'text-sidebar-foreground hover:bg-surface-subtle active:bg-surface-elevated' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="font-medium text-[14px]">กระเป๋าเงิน</span>
        </a>

        <!-- Transactions -->
        <a
            href="{{ route('transactions.index') }}"
            class="{{ request()->routeIs('transactions.*') ? 'bg-primary/10 text-primary' : 'text-sidebar-foreground hover:bg-surface-subtle active:bg-surface-elevated' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="font-medium text-[14px]">ธุรกรรม</span>
        </a>

        <!-- Categories -->
        <a
            href="{{ route('categories.index') }}"
            class="{{ request()->routeIs('categories.*') ? 'bg-primary/10 text-primary' : 'text-sidebar-foreground hover:bg-surface-subtle active:bg-surface-elevated' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group"
            :class="$store.sidebar.collapsed ? 'justify-center' : ''"
        >
            <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="font-medium text-[14px]">หมวดหมู่</span>
        </a>
    </nav>

    <!-- User profile section -->
    <div class="p-2.5 border-t border-sidebar-border shrink-0">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-2 rounded-xl transition-colors hover:bg-surface-subtle active:bg-surface-elevated group" :class="$store.sidebar.collapsed ? 'justify-center' : ''">
            <div class="h-9 w-9 rounded-full bg-primary/10 text-primary grid place-items-center font-semibold shrink-0 text-[15px] ring-2 ring-primary/20">
                {{ Auth::user()->name[0] ?? '?' }}
            </div>
            <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex-1 min-w-0">
                <div class="text-[13px] font-semibold text-foreground truncate leading-tight">{{ Auth::user()->name }}</div>
                <div class="text-[11px] text-text-muted truncate leading-tight">{{ Auth::user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline" x-show="!$store.sidebar.collapsed" @click.stop x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-text-muted hover:text-destructive hover:bg-destructive-light transition-colors focus:outline-none focus:ring-2 focus:ring-destructive"
                    aria-label="ออกจากระบบ"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </a>
    </div>
</aside>