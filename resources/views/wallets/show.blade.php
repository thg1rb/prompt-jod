<style>[x-cloak] { display: none !important; }</style>

<x-app-layout>
    <div class="space-y-6" x-data="walletShow()" x-init="initWallet('{{ $wallet->id }}', '{{ $wallet->type->value }}')">
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
                                @if($wallet->isOwner(auth()->user()))
                                    <button
                                        @click="$dispatch('open-share-modal')"
                                        class="inline-flex items-center px-4 py-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors font-medium"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                        แชร์
                                        @if($wallet->member_count > 0)
                                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-purple-100 dark:bg-purple-900/30">{{ $wallet->member_count }}</span>
                                        @endif
                                    </button>
                                @endif
                                <button
                                    @click="adjustOpen = true"
                                    class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg font-medium transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    ปรับยอด
                                </button>
                                <button
                                    @click="editOpen = true"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    แก้ไข
                                </button>
                                <button
                                    @click="deleteWallet()"
                                    class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors font-medium"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    ลบ
                                </button>
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
                        <button
                            @click="activeTab = 'transactions'"
                            :class="activeTab === 'transactions' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            ธุรกรรมล่าสุด
                        </button>
                        <button
                            @click="activeTab = 'adjustments'"
                            :class="activeTab === 'adjustments' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            ประวัติการปรับยอด ({{ $wallet->balanceAdjustments()->count() }})
                        </button>
                    </nav>
                </div>

                <!-- Transactions Tab -->
                <div x-show="activeTab === 'transactions'" class="bg-card border border-border rounded-lg p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">ธุรกรรมล่าสุด</h3>
                        <a href="{{ route('transactions.index') }}?wallet={{ $wallet->id }}" class="text-sm text-primary hover:underline">ดูทั้งหมด →</a>
                    </div>
                    <ul class="divide-y divide-border">
                        @if($wallet->transactions->isEmpty())
                            <li class="py-6 text-center text-text-muted text-sm">ยังไม่มีธุรกรรม</li>
                        @else
                            @foreach($wallet->transactions as $transaction)
                                @php
                                    $icon = $transaction->category?->icon ?? '📌';
                                @endphp
                                <li class="py-3 flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-surface-subtle grid place-items-center text-lg">{{ $icon }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $transaction->recipient ?? $transaction->category?->name ?? '-' }}</div>
                                        <div class="text-xs text-text-muted">{{ $transaction->category?->name ?? '-' }} · {{ $transaction->transacted_at?->diffForHumans() ?? '-' }}</div>
                                    </div>
                                    <div class="font-semibold {{ $transaction->isExpense() ? 'text-destructive' : 'text-success' }}">
                                        {{ $transaction->isExpense() ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <!-- Adjustments Tab -->
                <div x-show="activeTab === 'adjustments'" class="bg-card border border-border rounded-lg p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">ประวัติการปรับยอด</h3>
                        <a href="{{ route('wallets.adjustments', $wallet) }}" class="text-sm text-primary hover:underline">ดูทั้งหมด →</a>
                    </div>
                    <ul class="divide-y divide-border">
                        @if($wallet->balanceAdjustments->isEmpty())
                            <li class="py-6 text-center text-text-muted text-sm">ยังไม่มีการปรับยอด</li>
                        @else
                            @foreach($wallet->balanceAdjustments as $adjustment)
                                <li class="py-3 flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-surface-subtle grid place-items-center text-lg">🔄</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $adjustment->notes }}</div>
                                        <div class="text-xs text-text-muted">{{ $adjustment->adjusted_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="font-semibold {{ $adjustment->isIncrease() ? 'text-success' : 'text-destructive' }}">
                                        {{ $adjustment->isIncrease() ? '+' : '-' }}{{ number_format(abs($adjustment->adjustment_amount), 2) }}
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Adjust Balance Modal -->
        <div x-cloak x-show="adjustOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="adjustOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="adjustOpen = false"></div>

            <!-- Modal Content -->
            <div x-show="adjustOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card rounded-xl shadow-lg border border-border w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">ปรับยอดเงิน</h3>
                <div class="bg-muted rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">ยอดเงินปัจจุบัน</span>
                        <span class="text-lg font-semibold text-foreground">{{ number_format($wallet->balance, 2) }} บาท</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('wallets.adjust-balance', $wallet) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="new_balance" class="block text-sm font-medium text-foreground mb-1">ยอดเงินใหม่ (บาท)</label>
                        <input type="number" id="new_balance" name="new_balance" step="0.01" min="0" required class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                        @error('new_balance')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="adjust_notes" class="block text-sm font-medium text-foreground mb-1">เหตุผลการปรับ</label>
                        <textarea id="adjust_notes" name="notes" rows="2" required placeholder="เช่น ตรวจสอบยอดเงิน, โอนเงินระหว่างบัญชี" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"></textarea>
                        @error('notes')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="adjustOpen = false" class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground">
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors">
                            ปรับยอด
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Wallet Modal -->
        <div x-cloak x-show="editOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="editOpen = false"></div>

            <!-- Modal Content -->
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card rounded-xl shadow-lg border border-border w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">แก้ไขกระเป๋าเงิน</h3>
                <form method="POST" action="{{ route('wallets.update', $wallet) }}" class="space-y-3">
                    @csrf
                    @method('put')
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-foreground mb-1">ชื่อกระเป๋า</label>
                        <input type="text" id="edit_name" name="name" required value="{{ $wallet->name }}" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                        @error('name')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">ประเภท</label>
                        <select id="edit_type" name="type" x-model="draft.type" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                            <option value="bank">บัญชีธนาคาร</option>
                            <option value="ewallet">เว็บเวล็ต</option>
                            <option value="cash">เงินสด</option>
                        </select>
                        @error('type')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <template x-if="draft.type === 'bank'">
                        <div class="space-y-3">
                            <div>
                                <label for="edit_bank_name" class="block text-sm font-medium text-foreground mb-1">ชื่อธนาคาร</label>
                                <input type="text" id="edit_bank_name" name="bank_name" value="{{ $wallet->bank_name }}" placeholder="SCB, KBank, BBL..." class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                                @error('bank_name')
                                <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_account_number" class="block text-sm font-medium text-foreground mb-1">เลขที่บัญชี</label>
                                <input type="text" id="edit_account_number" name="account_number" value="{{ $wallet->account_number }}" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                                @error('account_number')
                                <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </template>
                    <div>
                        <label for="edit_notes" class="block text-sm font-medium text-foreground mb-1">หมายเหตุ</label>
                        <textarea id="edit_notes" name="notes" rows="3" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">{{ $wallet->notes }}</textarea>
                        @error('notes')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="edit_is_default" name="is_default" value="1" class="rounded border-border text-primary focus:ring-ring" @checked(old('is_default', $wallet->is_default)) />
                        <label for="edit_is_default" class="text-sm text-foreground">ตั้งเป็นกระเป๋าหลัก</label>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="editOpen = false" class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground">
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Wallet Share Modal (local to this page) -->
    <x-wallet-share :wallet-id="$wallet->id" :is-owner="$wallet->isOwner(auth()->user())" />

    @php
        $walletShareId = $wallet->id;
        $walletShareIsOwner = $wallet->isOwner(auth()->user());
    @endphp
</x-app-layout>
