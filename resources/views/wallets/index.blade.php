<x-app-layout>
    <div class="py-6" x-data="{ open: false, draft: { type: 'bank' } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-foreground">กระเป๋าเงิน</h1>
                <p class="text-muted-foreground text-sm">{{ $wallets->count() }} กระเป๋า · ยอดรวม {{ number_format($totalBalance, 2) }} บาท</p>
            </div>
            <button @click="open = true" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg font-medium transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                เพิ่มกระเป๋า
            </button>
        </div>

        <!-- Empty State -->
        @if($wallets->isEmpty())
            <div class="text-center py-12 bg-card rounded-xl border border-border">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 003-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="text-lg font-medium text-foreground mb-2">ยังไม่มีกระเป๋าเงิน</h3>
                <p class="text-muted-foreground mb-4">เริ่มต้นด้วยการเพิ่มกระเป๋าเงินแรกของคุณ</p>
            </div>
        @else
            <!-- Wallets Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($wallets as $wallet)
                    @php
                        $typeMeta = match($wallet->type->value) {
                            'bank' => [
                                'label' => 'ธนาคาร',
                                'color' => '#3B82F6',
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18" /></svg>'
                            ],
                            'ewallet' => [
                                'label' => 'e-Wallet',
                                'color' => '#8B5CF6',
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>'
                            ],
                            'cash' => [
                                'label' => 'เงินสด',
                                'color' => '#10B981',
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                            ],
                        };
                    @endphp
                    <div class="bg-card rounded-xl border border-border p-5 flex flex-col">
                        <!-- Wallet Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 rounded-lg grid place-items-center text-white" style="background-color: {{ $typeMeta['color'] }}">
                                    {!! $typeMeta['icon'] !!}
                                </div>
                                <div>
                                    <div class="font-semibold text-foreground">{{ $wallet->name }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ $typeMeta['label'] }}
                                        @if($wallet->bank_name)
                                            · {{ $wallet->bank_name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Balance -->
                        <div class="mt-4">
                            <div class="text-xs text-muted-foreground">ยอดคงเหลือ</div>
                            <div class="text-2xl font-bold text-foreground">{{ number_format($wallet->balance, 2) }} บาท</div>
                        </div>

                        <!-- Recent Transactions -->
                        <ul class="mt-4 space-y-2 text-sm flex-1">
                            @if($wallet->transactions->isNotEmpty())
                                @foreach($wallet->transactions->take(3) as $transaction)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="truncate text-secondary-foreground">{{ $transaction->recipient ?? $transaction->category?->name ?? '-' }}</span>
                                        <span class="font-medium shrink-0 {{ $transaction->isExpense() ? 'text-destructive' : 'text-success' }}">
                                            {{ $transaction->isExpense() ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-muted-foreground text-xs">ยังไม่มีรายการ</li>
                            @endif
                            @if($wallet->transactions->first())
                                <li class="text-[10px] text-muted-foreground">
                                    ล่าสุด: {{ $wallet->transactions->first()->transacted_at?->diffForHumans() ?? '-' }}
                                </li>
                            @endif
                        </ul>

                        <!-- Action Button -->
                        <a href="{{ route('wallets.show', $wallet) }}" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-sm font-medium text-foreground">
                            ดูทั้งหมด / ปรับยอด
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Create Wallet Modal -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <!-- Backdrop -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="open = false"></div>

            <!-- Modal Content -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card rounded-xl shadow-lg border border-border w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">เพิ่มกระเป๋าใหม่</h3>
                <form method="POST" action="{{ route('wallets.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="wname" class="block text-sm font-medium text-foreground mb-1">ชื่อกระเป๋า</label>
                        <input type="text" id="wname" name="name" required class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                        @error('name')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">ประเภท</label>
                        <select id="type" name="type" x-model="draft.type" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                            <option value="bank">ธนาคาร</option>
                            <option value="ewallet">e-Wallet</option>
                            <option value="cash">เงินสด</option>
                        </select>
                        @error('type')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <template x-if="draft.type === 'bank'">
                        <div class="space-y-3">
                            <div>
                                <label for="bank" class="block text-sm font-medium text-foreground mb-1">ชื่อธนาคาร</label>
                                <input type="text" id="bank" name="bank_name" placeholder="SCB, KBank, BBL..." class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                                @error('bank_name')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="account" class="block text-sm font-medium text-foreground mb-1">เลขที่บัญชี</label>
                                <input type="text" id="account" name="account_number" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                                @error('account_number')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </template>
                    <div>
                        <label for="bal" class="block text-sm font-medium text-foreground mb-1">ยอดเริ่มต้น</label>
                        <input type="number" id="bal" name="opening_balance" step="0.01" min="0" value="0" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background" />
                        @error('opening_balance')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-foreground mb-1">หมายเหตุ</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="เพิ่มบันทึกเกี่ยวกับกระเป๋าเงินนี้" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"></textarea>
                        @error('notes')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_default" name="is_default" class="rounded border-border text-primary focus:ring-ring" />
                        <label for="is_default" class="text-sm text-foreground">ตั้งเป็นกระเป๋าหลัก</label>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="open = false" class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground">
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
    </div>
</x-app-layout>
