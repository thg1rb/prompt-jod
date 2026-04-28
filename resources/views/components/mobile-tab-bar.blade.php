<nav
    aria-label="เมนูหลัก"
    class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-card border-t border-border pb-[env(safe-area-inset-bottom)]"
>
    <div class="grid grid-cols-5">
        <!-- Dashboard -->
        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>หน้าหลัก</span>
        </a>

        <!-- Wallets -->
        <a
            href="{{ route('wallets.index') }}"
            class="{{ request()->routeIs('wallets.*') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>กระเป๋า</span>
        </a>

        <!-- Add (Center placeholder) -->
        <a
            href="{{ route('wallets.create') }}"
            class="flex flex-col items-center justify-center py-2 text-xs text-text-muted"
        >
            <div class="-mt-6 h-12 w-12 rounded-full bg-primary text-primary-foreground grid place-items-center shadow-elevated hover:scale-105 transition-transform">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="text-[10px] mt-1">เพิ่ม</span>
        </a>

        <!-- Profile placeholder -->
        <a
            href="{{ route('profile.edit') }}"
            class="{{ request()->routeIs('profile.*') ? 'text-primary' : 'text-text-muted' }} flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>โปรไฟล์</span>
        </a>

        <!-- More (placeholder for future menu) -->
        <button
            class="text-text-muted flex flex-col items-center justify-center py-2.5 text-xs gap-0.5 transition-colors"
            aria-label="เมนูเพิ่มเติม"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span>เพิ่มเติม</span>
        </button>
    </div>
</nav>
