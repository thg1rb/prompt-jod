<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            ประวัติการปรับยอด: {{ $wallet->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Back Button -->
            <a href="{{ route('wallets.show', $wallet) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                กลับไปหน้าบัญชี
            </a>

            <!-- Adjustments List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                @if($adjustments->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">ไม่มีประวัติการปรับยอด</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($adjustments as $adjustment)
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="p-3 rounded-lg mt-1 {{ $adjustment->isIncrease() ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                            @if($adjustment->isIncrease())
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $adjustment->notes }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $adjustment->adjusted_at->format('d M Y, H:i:s') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="text-lg font-semibold {{ $adjustment->isIncrease() ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $adjustment->isIncrease() ? '+' : '-' }}{{ number_format(abs($adjustment->adjustment_amount), 2) }} บาท
                                        </p>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <span class="line-through">{{ number_format($adjustment->previous_balance, 2) }}</span>
                                            <span class="mx-1">→</span>
                                            <span class="font-medium">{{ number_format($adjustment->new_balance, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($adjustments->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $adjustments->appends(request()->query())->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
