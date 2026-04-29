<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ $wallet->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Back Button -->
            <a href="{{ route('wallets.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                กลับไปหน้ากระเป๋าเงินทั้งหมด
            </a>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Wallet Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @php
                    $headerColor = match($wallet->type->value) {
                        'bank' => '#3B82F6',
                        'ewallet' => '#8B5CF6',
                        'cash' => '#10B981',
                    };
                @endphp
                <!-- Wallet Header -->
                <div class="p-6" style="background-color: {{ $headerColor }}">
                    <div class="flex items-start justify-between">
                        <div>
                            @if($wallet->is_default)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white/20 text-white mb-2">
                                            กระเป๋าเงินหลัก
                                        </span>
                            @endif
                            <h1 class="text-2xl font-bold text-white">{{ $wallet->name }}</h1>
                            <p class="text-white/80 mt-1">{{ $wallet->type->getLabel() }}</p>
                        </div>
                    </div>
                    @if($wallet->bank_name)
                        <p class="text-white/70 text-sm mt-2">{{ $wallet->bank_name }}
                            @if($wallet->account_number)
                                • {{ $wallet->account_number }}
                            @endif
                        </p>
                    @endif
                </div>

                <!-- Wallet Body -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">ยอดคงเหลือ</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                                {{ number_format($wallet->balance, 2) }} บาท
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'adjust-balance' }))"
                                class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                ปรับยอด
                            </button>
                            <a href="{{ route('wallets.edit', $wallet) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                แก้ไข
                            </a>
                        </div>
                    </div>

                    @if($wallet->notes)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $wallet->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="#" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        ธุรกรรมล่าสุด
                    </a>
                    <a href="{{ route('wallets.adjustments', $wallet) }}" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        ประวัติการปรับยอด ({{ $wallet->balanceAdjustments()->count() }})
                    </a>
                </nav>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">ธุรกรรมล่าสุด</h3>

                @if($wallet->transactions->isEmpty())
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">ยังไม่มีธุรกรรม</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($wallet->transactions as $transaction)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-lg {{ $transaction->isExpense() ? 'bg-red-50 dark:bg-red-900/20' : ($transaction->isIncome() ? 'bg-green-50 dark:bg-green-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                                        @if($transaction->isExpense())
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        @elseif($transaction->isIncome())
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $transaction->description }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $transaction->transacted_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold {{ $transaction->isExpense() ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $transaction->isExpense() ? '-' : '+' }}{{ number_format($transaction->amount, 2) }} บาท
                                    </p>
                                    @if($transaction->category)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $transaction->category->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Balance Adjustments -->
            @if($recentAdjustments->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">การปรับยอดล่าสุด</h3>
                    <div class="space-y-3">
                        @foreach($recentAdjustments as $adjustment)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 rounded-lg {{ $adjustment->isIncrease() ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ $adjustment->isIncrease() ? 'text-green-500' : 'text-red-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $adjustment->notes }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $adjustment->adjusted_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold {{ $adjustment->isIncrease() ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $adjustment->isIncrease() ? '+' : '-' }}{{ number_format(abs($adjustment->adjustment_amount), 2) }} บาท
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ number_format($adjustment->previous_balance, 2) }} → {{ number_format($adjustment->new_balance, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Adjust Balance Modal -->
    <x-modal name="adjust-balance" maxWidth="md">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">ปรับยอดเงิน</h3>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">ยอดเงินปัจจุบัน</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($wallet->balance, 2) }} บาท</span>
                </div>
            </div>
            <form method="POST" action="{{ route('wallets.adjust-balance', $wallet) }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="new_balance" value="ยอดเงินใหม่ (บาท)" />
                    <x-text-input id="new_balance" name="new_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                    <x-input-error class="mt-2" :messages="$errors->get('new_balance')" />
                </div>
                <div>
                    <x-input-label for="notes" value="เหตุผลการปรับ" />
                    <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" rows="2" required placeholder="เช่น ตรวจสอบยอดเงิน, โอนเงินระหว่างบัญชี"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
                <div>
                    <x-input-label for="adjusted_at" value="วันที่ปรับ" />
                    <x-text-input id="adjusted_at" name="adjusted_at" type="datetime-local" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('adjusted_at')" />
                </div>
                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'adjust-balance' }))"
                        class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        ยกเลิก
                    </button>
                    <x-primary-button class="flex-1">ปรับยอด</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
