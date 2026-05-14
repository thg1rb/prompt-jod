<x-app-layout>
    <x-subscription-banner
        heading="ปลดล็อกฟีเจอร์ดึงข้อมูลจากรูปสลิป - เพียงแค่คุณสมัครสมาชิก"
        description="อัปโหลดสลิปเพื่อดึงข้อมูลแบบอัตโนมัติไม่ต้องกรอกข้อมูลเองให้เสียเวลา!"
    />
    <div
        x-data="transactions({{
            json_encode([
                'transactions' => $transactions,
                'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'icon' => $c->icon])->values(),
                'wallets' => $wallets->map(fn($w) => ['id' => $w->id, 'name' => $w->name])->values(),
            ])
        }})"
        @transaction-created.window="refresh()"
        @transaction-updated.window="refresh()"
        @transaction-deleted.window="refresh()"
        class="py-6"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-[22px] font-bold tracking-tight">ธุรกรรมทั้งหมด</h1>
                    <p class="text-text-muted text-sm"><span x-text="list.length"></span> รายการ</p>
                </div>
                <div class="flex gap-2.5">
                    <button
                        @click="$dispatch('open-transaction-modal')"
                        class="inline-flex items-center px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-semibold hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]"
                    >
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่มธุรกรรม
                    </button>
                    <button
                        @click="exportCsv()"
                        class="inline-flex items-center px-4 py-2.5 border border-border rounded-xl text-sm font-medium text-foreground hover:bg-surface-subtle active:bg-surface-elevated transition-colors"
                    >
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="grid gap-2.5 sm:grid-cols-[1fr,180px,180px]">
                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        x-model="q"
                        placeholder="ค้นหาธุรกรรม..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors placeholder-text-muted"
                    />
                </div>

                <!-- Category Filter -->
                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="w-full px-3 py-2.5 border border-border rounded-xl bg-card text-foreground text-left flex items-center justify-between text-sm font-medium hover:bg-surface-subtle transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        <span x-text="cat === 'all' ? 'ทุกหมวดหมู่' : categoryFor(cat)?.icon + ' ' + categoryFor(cat)?.name"></span>
                        <svg class="h-4 w-4 shrink-0 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-1 w-full bg-card border border-border rounded-xl shadow-floating py-1 max-h-56 overflow-auto"
                        style="display: none;"
                    >
                        <button
                            @click="cat = 'all'; open = false"
                            class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors font-medium"
                            :class="cat === 'all' ? 'bg-surface-subtle text-primary' : 'text-foreground'"
                        >
                            ทุกหมวดหมู่
                        </button>
                        <template x-for="c in categories" :key="c.id">
                            <button
                                @click="cat = c.id; open = false"
                                class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors flex items-center gap-2"
                                :class="cat === c.id ? 'bg-surface-subtle text-primary' : 'text-foreground'"
                            >
                                <span x-text="c.icon"></span>
                                <span x-text="c.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Wallet Filter -->
                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="w-full px-3 py-2.5 border border-border rounded-xl bg-card text-foreground text-left flex items-center justify-between text-sm font-medium hover:bg-surface-subtle transition-colors focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        <span x-text="wal === 'all' ? 'ทุกกระเป๋า' : walletFor(wal)?.name"></span>
                        <svg class="h-4 w-4 shrink-0 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-1 w-full bg-card border border-border rounded-xl shadow-floating py-1 max-h-56 overflow-auto"
                        style="display: none;"
                    >
                        <button
                            @click="wal = 'all'; open = false"
                            class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors font-medium"
                            :class="wal === 'all' ? 'bg-surface-subtle text-primary' : 'text-foreground'"
                        >
                            ทุกกระเป๋า
                        </button>
                        <template x-for="w in wallets" :key="w.id">
                            <button
                                @click="wal = w.id; open = false"
                                class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors"
                                :class="wal === w.id ? 'bg-surface-subtle text-primary' : 'text-foreground'"
                                x-text="w.name"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div x-show="loading" class="text-center py-14">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-text-muted text-sm">กำลังโหลด...</p>
            </div>

            <!-- Transaction List -->
            <div x-show="!loading" class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                <ul class="divide-y divide-border">
                    <template x-for="t in list" :key="t.id">
                        <li @click="viewTransaction(t)" class="p-4 flex items-center gap-3 cursor-pointer hover:bg-surface-subtle/50 transition-colors">
                            <div class="h-11 w-11 rounded-xl bg-surface-subtle grid place-items-center text-lg shrink-0" x-text="t.category_icon ?? '📌'"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-[14px] truncate" x-text="t.description"></div>
                                <div class="text-xs text-text-muted flex items-center gap-1.5">
                                    <span x-text="`${t.category} · ${t.wallet} · ${formatDateTime(t.transacted_at)}`"></span>
                                    <template x-if="t.wallet_access_type === 'shared' && t.creator_name">
                                        <span class="ml-1 px-1.5 py-0.5 rounded-md bg-primary/10 text-primary text-[11px] flex items-center gap-1 font-medium">
                                            <span x-text="t.creator_name.charAt(0).toUpperCase()"></span>
                                            <span x-text="`โดย ${t.creator_name}`"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div
                                class="font-semibold text-[14px] whitespace-nowrap"
                                :class="t.type === 'expense' ? 'text-destructive' : t.type === 'income' ? 'text-success' : 'text-text-secondary'"
                                x-text="`${t.type === 'expense' ? '-' : t.type === 'income' ? '+' : '±'}${formatTHB(t.amount)}`"
                            ></div>
                        </li>
                    </template>
                    <li x-show="list.length === 0" class="p-10 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-surface-subtle flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-text-muted text-sm">ไม่พบรายการ</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush