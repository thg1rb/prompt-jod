<nav
    aria-label="เมนูหลัก"
    class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-card border-t border-border pb-[env(safe-area-inset-bottom)]"
>
    <div class="grid grid-cols-5">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted-foreground' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? 'text-primary' : 'text-muted-foreground' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>กระเป๋าเงิน</span>
        </a>

        <!-- Add (Center placeholder) -->
        <button
            type="button"
            x-data="{}"
            @click="$dispatch('open-transaction-modal')"
            class="flex flex-col items-center justify-center py-2 text-xs text-muted-foreground bg-transparent border-0 cursor-pointer"
            aria-label="เพิ่มธุรกรรม"
        >
            <div class="-mt-6 h-12 w-12 rounded-full bg-primary text-primary-foreground grid place-items-center shadow-elevated hover:scale-105 transition-transform">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="text-[10px] mt-1">เพิ่มธุรกรรม</span>
        </button>

        <!-- Transactions -->
        <a
            href="{{ route('transactions.index') }}"
            class="{{ request()->routeIs('transactions.*') ? 'text-primary' : 'text-muted-foreground' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>ธุรกรรม</span>
        </a>

        <!-- Categories -->
        <a
            href="{{ route('categories.index') }}"
            class="{{ request()->routeIs('categories.*') ? 'text-primary' : 'text-muted-foreground' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span>หมวดหมู่</span>
        </a>
    </div>
</nav>
