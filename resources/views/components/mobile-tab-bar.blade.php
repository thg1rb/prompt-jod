<nav
    aria-label="เมนูหลัก"
    class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-surface-elevated/90 backdrop-blur-ios border-t border-border pt-0.5 pb-[calc(env(safe-area-inset-bottom)+0.5rem)]"
>
    <div class="grid grid-cols-5 h-[56px]">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center gap-0.5 transition-colors relative"
        >
            <div class="relative">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                @if(request()->routeIs('dashboard'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary"></span>
                @endif
            </div>
            <span class="text-[10px] font-medium">หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center gap-0.5 transition-colors relative"
        >
            <div class="relative">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                @if(request()->routeIs('wallets.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary"></span>
                @endif
            </div>
            <span class="text-[10px] font-medium">กระเป๋า</span>
        </a>

        <!-- Add (Center placeholder) -->
        <button
            type="button"
            x-data="{}"
            @click="$dispatch('open-transaction-modal')"
            class="flex flex-col items-center justify-center relative -mt-3"
            aria-label="เพิ่มธุรกรรม"
        >
            <div class="h-12 w-12 rounded-full bg-primary text-primary-foreground grid place-items-center shadow-floating hover:scale-105 active:scale-95 transition-all duration-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
        </button>

        <!-- Transactions -->
        <a
            href="{{ route('transactions.index') }}"
            class="{{ request()->routeIs('transactions.*') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center gap-0.5 transition-colors relative"
        >
            <div class="relative">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                @if(request()->routeIs('transactions.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary"></span>
                @endif
            </div>
            <span class="text-[10px] font-medium">ธุรกรรม</span>
        </a>

        <!-- Categories -->
        <a
            href="{{ route('categories.index') }}"
            class="{{ request()->routeIs('categories.*') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center gap-0.5 transition-colors relative"
        >
            <div class="relative">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                @if(request()->routeIs('categories.*'))
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary"></span>
                @endif
            </div>
            <span class="text-[10px] font-medium">หมวดหมู่</span>
        </a>
    </div>
</nav>