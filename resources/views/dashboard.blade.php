<x-app-layout>
    <x-subscription-banner
        heading="ปลดล็อกฟีเจอร์ทั้งหมด - เพียงแค่คุณสมัครสมาชิก"
        description="ก้าวข้ามข้อจำกัดเดิม ๆ และปลดล็อกฟีเจอร์ทั้งหมด เพื่อการใช้งานที่ลื่นไหลและครบยิ่งขึ้น"
    />

    <div x-data="dashboard({{ json_encode($dashboardData) }})" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-[22px] font-bold tracking-tight">แดชบอร์ด</h1>
                    <p class="text-text-muted text-sm">ภาพรวมการเงินของคุณ</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex bg-surface-subtle p-1 rounded-xl">
                        <button
                            @click="setRange('today')"
                            :class="range === 'today' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            วันนี้
                        </button>
                        <button
                            @click="setRange('week')"
                            :class="range === 'week' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            สัปดาห์นี้
                        </button>
                        <button
                            @click="setRange('month')"
                            :class="range === 'month' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            เดือนนี้
                        </button>
                        <button
                            @click="setRange('all')"
                            :class="range === 'all' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            ทั้งหมด
                        </button>
                    </div>
                    <div class="inline-flex bg-surface-subtle p-1 rounded-xl">
                        <button
                            @click="setWalletType('all')"
                            :class="walletType === 'all' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            ทุกกระเป๋า
                        </button>
                        <button
                            @click="setWalletType('personal')"
                            :class="walletType === 'personal' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            ส่วนตัว
                        </button>
                        <button
                            @click="setWalletType('shared')"
                            :class="walletType === 'shared' ? 'bg-card text-foreground shadow-sm' : 'text-text-muted hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            แชร์
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="text-center py-14">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-3">
                    <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-text-muted text-sm">กำลังโหลดข้อมูล...</p>
            </div>

            <div x-show="!loading" x-cloak class="space-y-5">
                <!-- Summary Cards -->
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Expenses -->
                    <div class="bg-card rounded-2xl border border-border p-4 shadow-card">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-destructive/10 flex items-center justify-center">
                                <svg class="h-4 w-4 text-destructive" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-text-muted">ยอดใช้จ่าย</span>
                        </div>
                        <div class="text-[22px] font-bold tracking-tight" x-text="formatAmount(totalExpenses)"></div>
                        <div class="text-[11px] text-text-muted mt-1" x-text="`${filteredCount} ธุรกรรม`"></div>
                    </div>

                    <!-- Total Balance -->
                    <div class="bg-card rounded-2xl border border-border p-4 shadow-card">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
                                <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-text-muted">ยอดคงเหลือรวม</span>
                        </div>
                        <div class="text-[22px] font-bold tracking-tight" x-text="formatAmount(totalBalance)"></div>
                        <div class="text-[11px] text-text-muted mt-1" x-text="`${walletCount} กระเป๋า`"></div>
                    </div>

                    <!-- Average Per Transaction -->
                    <div class="bg-card rounded-2xl border border-border p-4 shadow-card">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center">
                                <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-text-muted">เฉลี่ยต่อรายการ</span>
                        </div>
                        <div class="text-[22px] font-bold tracking-tight" x-text="formatAmount(averagePerTransaction)"></div>
                        <div class="text-[11px] text-text-muted mt-1">ในช่วงที่เลือก</div>
                    </div>

                    <!-- Top Category -->
                    <div class="bg-card rounded-2xl border border-border p-4 shadow-card">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-warning/10 flex items-center justify-center">
                                <svg class="h-4 w-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-text-muted">หมวดสูงสุด</span>
                        </div>
                        <div class="text-[17px] font-bold tracking-tight truncate" x-text="topCategory ? `${topCategory.icon} ${topCategory.name}` : '—'"></div>
                        <div class="text-[11px] text-text-muted mt-1" x-text="topCategory ? formatAmount(topCategory.value) : 'ยังไม่มีข้อมูล'"></div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Pie Chart -->
                    <div class="bg-card rounded-2xl border border-border p-5 shadow-card">
                        <h3 class="font-semibold text-[15px] mb-4">สัดส่วนรายจ่ายตามหมวดหมู่</h3>
                        <div x-show="categoryData.length === 0" class="h-56 grid place-items-center">
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-2xl bg-surface-subtle flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 11V9a2 2 0 10-4 0v2a2 2 0 10-4 0v2a2 2 0 104 0v2a2 2 0 104 0v1" />
                                    </svg>
                                </div>
                                <p class="text-text-muted text-sm">ยังไม่มีรายจ่ายในช่วงนี้</p>
                            </div>
                        </div>
                        <div x-show="categoryData.length > 0">
                            <div x-data="pieChart({ categories: categoryData })" class="h-56"></div>
                            <ul class="mt-4 space-y-2.5">
                                <template x-for="cat in categoryData.slice(0, 5)" :key="cat.id">
                                    <li class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full" :style="{ background: cat.color }"></span>
                                            <span x-text="`${cat.icon} ${cat.name}`"></span>
                                        </span>
                                        <span class="font-semibold" x-text="formatAmount(cat.value)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    <div class="bg-card rounded-2xl border border-border p-5 shadow-card">
                        <h3 class="font-semibold text-[15px] mb-4">รายจ่าย 7 วันที่ผ่านมา</h3>
                        <div x-data="barChart({ data: sevenDaySpending })" class="h-56"></div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                        <h3 class="font-semibold text-[15px]">ธุรกรรมล่าสุด</h3>
                        <a href="/transactions" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors">ดูทั้งหมด →</a>
                    </div>
                    <ul class="divide-y divide-border">
                        <li x-show="recentTransactions.length === 0" class="py-8 text-center text-text-muted text-sm">ยังไม่มีธุรกรรม</li>
                        <template x-for="transaction in recentTransactions" :key="transaction.id">
                            <li class="py-3.5 px-5 flex items-center gap-3 hover:bg-surface-subtle/50 transition-colors">
                                <div class="h-10 w-10 rounded-xl bg-surface-subtle grid place-items-center text-lg shrink-0" x-text="transaction.icon"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-[14px] truncate" x-text="transaction.recipient"></div>
                                    <div class="text-xs text-text-muted" x-text="`${transaction.category} · ${transaction.wallet} · ${formatRelativeTime(transaction.transacted_at)}`"></div>
                                </div>
                                <div class="font-semibold text-[14px]" :class="transaction.type === 'expense' ? 'text-destructive' : 'text-success'" x-text="`${transaction.type === 'expense' ? '-' : '+'}${formatAmount(transaction.amount)}`"></div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>