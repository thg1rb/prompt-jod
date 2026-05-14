<style>[x-cloak] { display: none !important; }</style>

<x-app-layout>
    <div class="space-y-5" x-data="walletShow()" x-init="initWallet('{{ $wallet->id }}', '{{ $wallet->type->value }}')">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Back Button -->
            <a href="{{ route('wallets.index') }}" class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                กลับไปหน้ากระเป๋าเงินทั้งหมด
            </a>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-success-light border border-success/20 rounded-xl p-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-success text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Wallet Card -->
            @php
                $headerColor = match($wallet->type->value) {
                    'bank' => '#007AFF',
                    'ewallet' => '#5856D6',
                    'cash' => '#34C759',
                };
            @endphp
            <div class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                <!-- Wallet Header -->
                <div class="p-5 sm:p-6" style="background-color: {{ $headerColor }}">
                    <div class="flex items-start justify-between">
                        <div>
                            @if($wallet->is_default)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/20 text-white mb-2">
                                    กระเป๋าเงินหลัก
                                </span>
                            @endif
                            <h1 class="text-xl font-bold text-white tracking-tight">{{ $wallet->name }}</h1>
                            <p class="text-white/70 text-sm mt-0.5">{{ $wallet->type->getLabel() }}</p>
                        </div>
                    </div>
                    @if($wallet->bank_name)
                        <p class="text-white/60 text-sm mt-2">{{ $wallet->bank_name }}
                            @if($wallet->account_number)
                                · {{ $wallet->account_number }}
                            @endif
                        </p>
                    @endif
                </div>

                <!-- Wallet Body -->
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <p class="text-text-muted text-sm font-medium">ยอดคงเหลือ</p>
                            <p class="text-3xl font-bold tracking-tight mt-0.5">
                                {{ number_format($wallet->balance, 2) }} บาท
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($wallet->isOwner(auth()->user()) && $wallet->access_type->value === 'shared')
                                <button
                                    @click="$dispatch('open-share-modal')"
                                    class="inline-flex items-center px-4 py-2.5 bg-primary/10 text-primary rounded-xl hover:bg-primary/20 transition-colors font-semibold text-sm"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    แชร์
                                    @if($wallet->member_count > 0)
                                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-primary/20">{{ $wallet->member_count }}</span>
                                    @endif
                                </button>
                            @endif
                            <button
                                @click="adjustOpen = true"
                                class="inline-flex items-center px-4 py-2.5 bg-primary text-primary-foreground rounded-xl font-semibold text-sm hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                ปรับยอด
                            </button>
                            <button
                                @click="editOpen = true"
                                class="inline-flex items-center px-4 py-2.5 bg-surface-subtle text-foreground rounded-xl font-medium text-sm hover:bg-surface-elevated transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                แก้ไข
                            </button>
                            <button
                                @click="deleteWallet()"
                                class="inline-flex items-center px-4 py-2.5 bg-destructive-light text-destructive rounded-xl font-medium text-sm hover:bg-destructive/20 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                ลบ
                            </button>
                        </div>
                    </div>

                    @if($wallet->notes)
                        <div class="bg-surface-subtle rounded-xl p-3.5">
                            <p class="text-sm text-text-muted">{{ $wallet->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-border">
                <nav class="flex gap-5" aria-label="Tabs">
                    <button
                        @click="activeTab = 'transactions'"
                        :class="activeTab === 'transactions' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-foreground'"
                        class="whitespace-nowrap py-3 px-0.5 border-b-2 font-semibold text-sm transition-colors"
                    >
                        ธุรกรรมล่าสุด
                    </button>
                    <button
                        @click="activeTab = 'adjustments'"
                        :class="activeTab === 'adjustments' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-foreground'"
                        class="whitespace-nowrap py-3 px-0.5 border-b-2 font-semibold text-sm transition-colors"
                    >
                        ประวัติการปรับยอด ({{ $wallet->balanceAdjustments()->count() }})
                    </button>
                </nav>
            </div>

            <!-- Transactions Tab -->
            <div x-show="activeTab === 'transactions'" class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h3 class="font-semibold text-[15px]">ธุรกรรมล่าสุด</h3>
                    <a href="{{ route('transactions.index') }}?wallet={{ $wallet->id }}" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors">ดูทั้งหมด →</a>
                </div>
                <ul class="divide-y divide-border">
                    @if($wallet->transactions->isEmpty())
                        <li class="py-6 text-center text-text-muted text-sm">ยังไม่มีธุรกรรม</li>
                    @else
                        @foreach($wallet->transactions as $transaction)
                            @php
                                $icon = $transaction->category?->icon ?? '📌';
                            @endphp
                            <li class="py-3.5 px-5 flex items-center gap-3">
                                <div class="h-11 w-11 rounded-xl bg-surface-subtle grid place-items-center text-lg shrink-0">{{ $icon }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-[14px] truncate">{{ $transaction->recipient ?? $transaction->category?->name ?? '-' }}</div>
                                    <div class="text-xs text-text-muted">{{ $transaction->category?->name ?? '-' }} · {{ $transaction->transacted_at?->diffForHumans() ?? '-' }}</div>
                                </div>
                                <div class="font-semibold text-[14px] {{ $transaction->isExpense() ? 'text-destructive' : 'text-success' }}">
                                    {{ $transaction->isExpense() ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <!-- Adjustments Tab -->
            <div x-show="activeTab === 'adjustments'" class="bg-card rounded-2xl border border-border overflow-hidden shadow-card">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h3 class="font-semibold text-[15px]">ประวัติการปรับยอด</h3>
                    <a href="{{ route('wallets.adjustments', $wallet) }}" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors">ดูทั้งหมด →</a>
                </div>
                <ul class="divide-y divide-border">
                    @if($wallet->balanceAdjustments->isEmpty())
                        <li class="py-6 text-center text-text-muted text-sm">ยังไม่มีการปรับยอด</li>
                    @else
                        @foreach($wallet->balanceAdjustments as $adjustment)
                            <li class="py-3.5 px-5 flex items-center gap-3">
                                <div class="h-11 w-11 rounded-xl bg-surface-subtle grid place-items-center text-lg shrink-0">🔄</div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-[14px] truncate">{{ $adjustment->notes }}</div>
                                    <div class="text-xs text-text-muted">{{ $adjustment->adjusted_at->diffForHumans() }}</div>
                                </div>
                                <div class="font-semibold text-[14px] {{ $adjustment->isIncrease() ? 'text-success' : 'text-destructive' }}">
                                    {{ $adjustment->isIncrease() ? '+' : '-' }}{{ number_format(abs($adjustment->adjustment_amount), 2) }}
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>

        <!-- Adjust Balance Modal -->
        <div x-cloak x-show="adjustOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="adjustOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="adjustOpen = false"></div>
            <div x-show="adjustOpen" x-transition:enter="transition ease-out duration-350" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-250" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full max-w-md p-6">
                <h3 class="text-[17px] font-semibold text-foreground mb-4">ปรับยอดเงิน</h3>
                <div class="bg-surface-subtle rounded-xl p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-text-muted">ยอดเงินปัจจุบัน</span>
                        <span class="text-lg font-bold text-foreground">{{ number_format($wallet->balance, 2) }} บาท</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('wallets.adjust-balance', $wallet) }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label for="new_balance" class="block text-sm font-semibold text-foreground mb-1.5">ยอดเงินใหม่ (บาท)</label>
                        <input type="number" id="new_balance" name="new_balance" step="0.01" min="0" required class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                        @error('new_balance')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="adjust_notes" class="block text-sm font-semibold text-foreground mb-1.5">เหตุผลการปรับ</label>
                        <textarea id="adjust_notes" name="notes" rows="2" required placeholder="เช่น ตรวจสอบยอดเงิน, โอนเงินระหว่างบัญชี" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors resize-none"></textarea>
                        @error('notes')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="adjustOpen = false" class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm">
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm shadow-sm">
                            ปรับยอด
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Wallet Modal -->
        <div x-cloak x-show="editOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="editOpen = false"></div>
            <div x-show="editOpen" x-transition:enter="transition ease-out duration-350" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-250" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full max-w-md p-6">
                <h3 class="text-[17px] font-semibold text-foreground mb-4">แก้ไขกระเป๋าเงิน</h3>
                <form method="POST" action="{{ route('wallets.update', $wallet) }}" class="space-y-3.5">
                    @csrf
                    @method('put')
                    <div>
                        <label for="edit_name" class="block text-sm font-semibold text-foreground mb-1.5">ชื่อกระเป๋า</label>
                        <input type="text" id="edit_name" name="name" required value="{{ $wallet->name }}" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                        @error('name')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-foreground mb-1.5">ประเภท</label>
                        <select id="edit_type" name="type" x-model="draft.type" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors">
                            <option value="bank">บัญชีธนาคาร</option>
                            <option value="ewallet">เว็บเวล็ต</option>
                            <option value="cash">เงินสด</option>
                        </select>
                        @error('type')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <template x-if="draft.type === 'bank'">
                        <div class="space-y-3.5">
                            <div>
                                <label for="edit_bank_name" class="block text-sm font-semibold text-foreground mb-1.5">ชื่อธนาคาร</label>
                                <input type="text" id="edit_bank_name" name="bank_name" value="{{ $wallet->bank_name }}" placeholder="SCB, KBank, BBL..." class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                @error('bank_name')
                                <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_account_number" class="block text-sm font-semibold text-foreground mb-1.5">เลขที่บัญชี</label>
                                <input type="text" id="edit_account_number" name="account_number" value="{{ $wallet->account_number }}" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                @error('account_number')
                                <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </template>
                    <div>
                        <label for="edit_notes" class="block text-sm font-semibold text-foreground mb-1.5">หมายเหตุ</label>
                        <textarea id="edit_notes" name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors resize-none">{{ $wallet->notes }}</textarea>
                        @error('notes')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" id="edit_is_default" name="is_default" value="1" class="w-4 h-4 rounded border-border text-primary focus:ring-ring" @checked(old('is_default', $wallet->is_default)) />
                        <label for="edit_is_default" class="text-sm font-medium text-foreground">ตั้งเป็นกระเป๋าหลัก</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="editOpen = false" class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm">
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm shadow-sm">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-wallet-share :wallet-id="$wallet->id" :is-owner="$wallet->isOwner(auth()->user())" />

    @php
        $walletShareId = $wallet->id;
        $walletShareIsOwner = $wallet->isOwner(auth()->user());
    @endphp
</x-app-layout>