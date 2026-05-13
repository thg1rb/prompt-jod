<x-app-layout>
    @if(auth()->user()->isFree())
    <div x-data="{ showBanner: true }" x-init="showBanner = !$paywall?.dismissed; window.addEventListener('paywall-dismissed', () => showBanner = false)">
        <div x-show="showBanner" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-4">
            <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-primary/10 border border-primary/20 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground">ปลดล็อกฟีเจอร์ทั้งหมด — สมัครสมาชิก Premium</p>
                        <p class="text-sm text-muted-foreground">กระเป๋าเงินไม่จำกัด, OCR สลิป, ธุรกรรมในกระเป๋าแชร์ และอีกมากมาย</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="$paywall?.open()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg font-medium text-sm transition-colors">
                        สมัครสมาชิก
                    </button>
                    <button @click="showBanner = false; $paywall?.dismiss()" class="p-2 text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div x-data="dashboard({{ json_encode($dashboardData) }})" class="py-6">
        <!-- Debug: {{ json_encode($dashboardData) }} -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">แดชบอร์ด</h1>
                    <p class="text-text-muted text-sm">ภาพรวมการเงินของคุณ</p>
                </div>
                <div class="inline-flex w-fit bg-muted p-1 rounded-lg">
                    <button
                        @click="setRange('today')"
                        :class="range === 'today' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    >
                        วันนี้
                    </button>
                    <button
                        @click="setRange('week')"
                        :class="range === 'week' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    >
                        สัปดาห์นี้
                    </button>
                    <button
                        @click="setRange('month')"
                        :class="range === 'month' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    >
                        เดือนนี้
                    </button>
                    <button
                        @click="setRange('all')"
                        :class="range === 'all' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    >
                        ทั้งหมด
                    </button>
                </div>
            </div>

            <div x-show="loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-primary mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-text-muted mt-2">กำลังโหลดข้อมูล...</p>
            </div>

            <div x-show="!loading" x-cloak class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="bg-card border border-border rounded-lg p-5">
                        <div class="flex items-center gap-2 text-text-muted text-sm">
                            <svg class="h-4 w-4 text-destructive" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            ยอดใช้จ่าย
                        </div>
                        <div class="mt-2 text-2xl font-bold" x-text="formatAmount(totalExpenses)"></div>
                        <div class="text-xs text-text-muted mt-1" x-text="`${filteredCount} ธุรกรรม`"></div>
                    </div>
                    <div class="bg-card border border-border rounded-lg p-5">
                        <div class="flex items-center gap-2 text-text-muted text-sm">
                            <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            ยอดคงเหลือรวม
                        </div>
                        <div class="mt-2 text-2xl font-bold" x-text="formatAmount(totalBalance)"></div>
                        <div class="text-xs text-text-muted mt-1" x-text="`${walletCount} กระเป๋า`"></div>
                    </div>
                    <div class="bg-card border border-border rounded-lg p-5">
                        <div class="flex items-center gap-2 text-text-muted text-sm">
                            <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            เฉลี่ยต่อรายการ
                        </div>
                        <div class="mt-2 text-2xl font-bold" x-text="formatAmount(averagePerTransaction)"></div>
                        <div class="text-xs text-text-muted mt-1">ในช่วงที่เลือก</div>
                    </div>
                    <div class="bg-card border border-border rounded-lg p-5">
                        <div class="flex items-center gap-2 text-text-muted text-sm">
                            <svg class="h-4 w-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                            หมวดสูงสุด
                        </div>
                        <div class="mt-2 text-lg font-bold truncate" x-text="topCategory ? `${topCategory.icon} ${topCategory.name}` : '—'"></div>
                        <div class="text-xs text-text-muted mt-1" x-text="topCategory ? formatAmount(topCategory.value) : 'ยังไม่มีข้อมูล'"></div>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="bg-card border border-border rounded-lg p-5">
                        <h3 class="font-semibold mb-4">สัดส่วนรายจ่ายตามหมวดหมู่</h3>
                        <div x-show="categoryData.length === 0" class="h-64 grid place-items-center text-text-muted text-sm">ยังไม่มีรายจ่ายในช่วงนี้</div>
                        <div x-show="categoryData.length > 0">
                            <div x-data="pieChart({ categories: categoryData })" class="h-64"></div>
                            <ul class="mt-4 space-y-2">
                                <template x-for="cat in categoryData.slice(0, 5)" :key="cat.id">
                                    <li class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full" :style="{ background: cat.color }"></span>
                                            <span x-text="`${cat.icon} ${cat.name}`"></span>
                                        </span>
                                        <span class="font-medium" x-text="formatAmount(cat.value)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-lg p-5">
                        <h3 class="font-semibold mb-4">รายจ่าย 7 วันที่ผ่านมา</h3>
                        <div x-data="barChart({ data: sevenDaySpending })" class="h-64"></div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-lg p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">ธุรกรรมล่าสุด</h3>
                        <a href="/transactions" class="text-sm text-primary hover:underline">ดูทั้งหมด →</a>
                    </div>
                    <ul class="divide-y divide-border">
                        <li x-show="recentTransactions.length === 0" class="py-6 text-center text-text-muted text-sm">ยังไม่มีธุรกรรม</li>
                        <template x-for="transaction in recentTransactions" :key="transaction.id">
                            <li class="py-3 flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-surface-subtle grid place-items-center text-lg" x-text="transaction.icon"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate" x-text="transaction.recipient"></div>
                                    <div class="text-xs text-text-muted" x-text="`${transaction.category} · ${transaction.wallet} · ${formatRelativeTime(transaction.transacted_at)}`"></div>
                                </div>
                                <div class="font-semibold" :class="transaction.type === 'expense' ? 'text-destructive' : 'text-success'" x-text="`${transaction.type === 'expense' ? '-' : '+'}${formatAmount(transaction.amount)}`"></div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
