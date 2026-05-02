<style>[x-cloak] { display: none !important; }</style>

<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Back Button -->
            <a href="{{ route('wallets.show', $wallet) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                กลับไปหน้ากระเป๋าเงิน
            </a>

            <!-- Wallet Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @php
                    $headerColor = match($wallet->type->value) {
                        'bank' => '#3B82F6',
                        'ewallet' => '#8B5CF6',
                        'cash' => '#10B981',
                    };
                @endphp
                <div class="p-6" style="background-color: {{ $headerColor }}">
                    <h1 class="text-xl font-bold text-white">{{ $wallet->name }} - ประวัติการปรับยอด</h1>
                    <p class="text-white/80 mt-1">{{ $wallet->type->getLabel() }}</p>
                </div>
            </div>

            <!-- Adjustments List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">ประวัติการปรับยอดทั้งหมด</h2>
                </div>

                @if($adjustments->isEmpty())
                    <div class="p-12 text-center">
                        <div class="text-gray-400 dark:text-gray-600 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">ยังไม่มีการปรับยอด</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($adjustments as $adjustment)
                            <div class="p-4 sm:p-6 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 grid place-items-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $adjustment->notes }}</p>
                                        <span class="text-sm font-semibold {{ $adjustment->isIncrease() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $adjustment->isIncrease() ? '+' : '-' }}{{ number_format(abs($adjustment->adjustment_amount), 2) }} บาท
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $adjustment->adjusted_at->format('d M Y, H:i') }}</span>
                                        <span>ยอด: {{ number_format($adjustment->new_balance, 2) }} บาท</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Pagination -->
                @if($adjustments->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $adjustments->appends(['wallet' => $wallet->id])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
