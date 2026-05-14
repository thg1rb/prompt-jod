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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">ธุรกรรมทั้งหมด</h1>
                    <p class="text-text-muted text-sm"><span x-text="list.length"></span> รายการ</p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="$dispatch('open-transaction-modal')"
                        class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors"
                    >
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่มธุรกรรม
                    </button>
                    <button
                        @click="exportCsv()"
                        class="inline-flex items-center px-3 py-2 border border-border rounded-lg text-sm hover:bg-muted transition-colors text-foreground"
                    >
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-[1fr,200px,200px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        x-model="q"
                        placeholder="ค้นหา..."
                        class="w-full pl-9 px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                    />
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                    >
                        <span x-text="cat === 'all' ? 'ทุกหมวดหมู่' : categoryFor(cat)?.icon + ' ' + categoryFor(cat)?.name"></span>
                        <svg class="h-4 w-4 ml-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-1 w-full bg-card border border-border rounded-lg shadow-lg py-1 max-h-60 overflow-auto"
                        style="display: none;"
                    >
                        <button
                            @click="cat = 'all'; open = false"
                            class="w-full px-3 py-2 text-left hover:bg-muted text-foreground text-sm"
                            :class="cat === 'all' ? 'bg-muted' : ''"
                        >
                            ทุกหมวดหมู่
                        </button>
                        <template x-for="c in categories" :key="c.id">
                            <button
                                @click="cat = c.id; open = false"
                                class="w-full px-3 py-2 text-left hover:bg-muted text-foreground text-sm flex items-center gap-2"
                                :class="cat === c.id ? 'bg-muted' : ''"
                            >
                                <span x-text="c.icon"></span>
                                <span x-text="c.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button
                        @click="open = !open"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                    >
                        <span x-text="wal === 'all' ? 'ทุกกระเป๋า' : walletFor(wal)?.name"></span>
                        <svg class="h-4 w-4 ml-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-1 w-full bg-card border border-border rounded-lg shadow-lg py-1 max-h-60 overflow-auto"
                        style="display: none;"
                    >
                        <button
                            @click="wal = 'all'; open = false"
                            class="w-full px-3 py-2 text-left hover:bg-muted text-foreground text-sm"
                            :class="wal === 'all' ? 'bg-muted' : ''"
                        >
                            ทุกกระเป๋า
                        </button>
                        <template x-for="w in wallets" :key="w.id">
                            <button
                                @click="wal = w.id; open = false"
                                class="w-full px-3 py-2 text-left hover:bg-muted text-foreground text-sm"
                                :class="wal === w.id ? 'bg-muted' : ''"
                                x-text="w.name"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-primary mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-text-muted mt-2">กำลังโหลด...</p>
            </div>

            <div x-show="!loading" class="bg-card border border-border rounded-lg overflow-hidden">
                <ul class="divide-y divide-border">
                    <template x-for="t in list" :key="t.id">
                        <li @click="viewTransaction(t)" class="p-4 flex items-center gap-3 cursor-pointer hover:bg-muted/50 transition-colors">
                            <div class="h-10 w-10 rounded-lg bg-surface-subtle grid place-items-center text-lg" x-text="t.category_icon ?? '📌'"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate" x-text="t.description"></div>
                                <div class="text-xs text-text-muted flex items-center gap-1">
                                    <span x-text="`${t.category} · ${t.wallet} · ${formatDateTime(t.transacted_at)}`"></span>
                                    <template x-if="t.wallet_access_type === 'shared' && t.creator_name">
                                        <span class="ml-1 px-1 py-0.5 rounded bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs flex items-center gap-1">
                                            <span x-text="t.creator_name.charAt(0).toUpperCase()"></span>
                                            <span x-text="`โดย ${t.creator_name}`"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div
                                class="font-semibold whitespace-nowrap"
                                :class="t.type === 'expense' ? 'text-destructive' : t.type === 'income' ? 'text-success' : 'text-text-secondary'"
                                x-text="`${t.type === 'expense' ? '-' : t.type === 'income' ? '+' : '±'}${formatTHB(t.amount)}`"
                            ></div>
                        </li>
                    </template>
                    <li x-show="list.length === 0" class="p-10 text-center text-text-muted">ไม่พบรายการ</li>
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
