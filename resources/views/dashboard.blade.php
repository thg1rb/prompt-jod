<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            แดชบอร์ด
        </h2>
    </x-slot>

    <div x-data="dashboard()" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">แดชบอร์ด</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">ภาพรวมการเงินของคุณ</p>
            </div>

            <!-- Time Range Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2 inline-flex gap-2">
                <button
                    @click="changeRange('today')"
                    :class="range === 'today' ? 'bg-indigo-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    วันนี้
                </button>
                <button
                    @click="changeRange('week')"
                    :class="range === 'week' ? 'bg-indigo-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    สัปดาห์นี้
                </button>
                <button
                    @click="changeRange('month')"
                    :class="range === 'month' ? 'bg-indigo-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    เดือนนี้
                </button>
                <button
                    @click="changeRange('all')"
                    :class="range === 'all' ? 'bg-indigo-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    ทั้งหมด
                </button>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="text-center py-8">
                <svg class="animate-spin h-8 w-8 text-indigo-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 mt-2">กำลังโหลดข้อมูล...</p>
            </div>

            <!-- Error State -->
            <div x-show="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-red-600 dark:text-red-400">
                <p x-text="error"></p>
            </div>

            <!-- Summary Cards -->
            <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Expenses -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ยอดใช้จ่าย</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                                <span x-text="formatAmount(totalExpenses)"></span> บาท
                            </p>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Balance -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ยอดคงเหลือรวม</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                                <span x-text="formatAmount(totalBalance)"></span> บาท
                            </p>
                        </div>
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Per Transaction -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">เฉลี่ยต่อรายการ</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                                <span x-text="formatAmount(averagePerTransaction)"></span> บาท
                            </p>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Top Category -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">หมวดสูงสุด</p>
                            <template x-if="topCategory">
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1" x-text="topCategory.name"></p>
                            </template>
                            <template x-if="!topCategory">
                                <p class="text-lg font-medium text-gray-400 dark:text-gray-500 mt-1">-</p>
                            </template>
                        </div>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pie Chart - Category Breakdown -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">สัดส่วนรายจ่ายตามหมวดหมู่</h3>
                    <div x-data="pieChart({ categories: $parent.categoryData })" x-init="init()">
                        <div></div>
                    </div>
                    <template x-if="categoryData.length === 0">
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            ไม่มีข้อมูลรายจ่าย
                        </div>
                    </template>
                </div>

                <!-- Bar Chart - 7 Day Trend -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">แนวโน้มรายจ่าย 7 วัน</h3>
                    <div x-data="barChart({ data: $parent.sevenDaySpending })" x-init="init()">
                        <div></div>
                    </div>
                    <template x-if="sevenDaySpending.every(d => d.amount === 0)">
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            ไม่มีข้อมูลรายจ่าย
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div x-show="!loading" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">ธุรกรรมล่าสุด</h3>

                <template x-if="recentTransactions.length === 0">
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">ยังไม่มีธุรกรรม</p>
                    </div>
                </template>

                <template x-if="recentTransactions.length > 0">
                    <div class="space-y-3">
                        <template x-for="transaction in recentTransactions" :key="transaction.id">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div x-html="getTransactionIcon(transaction.type)"></div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100" x-text="transaction.description"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                            <span x-text="transaction.category"></span>
                                            <span class="mx-2">•</span>
                                            <span x-text="transaction.wallet"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold" :class="transaction.type === 'expense' ? 'text-red-500' : 'text-green-500'">
                                        <span x-text="transaction.type === 'expense' ? '-' : '+'"></span>
                                        <span x-text="formatAmount(transaction.amount)"></span> บาท
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="formatRelativeTime(transaction.transacted_at)"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div x-show="recentTransactions.length > 0" class="mt-4 text-center">
                    <a href="/transactions" class="inline-flex items-center text-sm font-medium text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300">
                        ดูธุรกรรมทั้งหมด
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
