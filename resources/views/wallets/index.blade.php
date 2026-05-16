<x-app-layout>
    @php
        $user = auth()->user();
        $isFree = $user->isFree();
        $ownedWalletCount = $user->ownedWalletCount();
        $atWalletLimit = $isFree && $ownedWalletCount >= 5;
    @endphp
    <x-subscription-banner
        heading="ปลดล็อกฟีเจอร์จัดการกระเป๋าเต็มรูปแบบ - เพียงแค่คุณสมัครสมาชิก"
        description="สร้างหรือเข้าร่วมกระเป๋าเงินแบบแชร์ร่วมกับคนที่คุณต้องการ และสร้างกระเป๋าเงินได้ไม่จำกัดจำนวน!"
    />
    <div class="py-6" x-data="{ open: false, draft: { type: 'bank' }, atLimit: {{ $atWalletLimit ? 'true' : 'false' }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-[22px] font-bold tracking-tight">กระเป๋าเงิน</h1>
                    <p class="text-text-muted text-sm">{{ $wallets->count() }} กระเป๋า · ยอดรวม {{ number_format($totalBalance, 2) }} บาท</p>
                </div>
                <button @click="{{ $atWalletLimit ? '$paywall?.open()' : 'open = true' }}" class="inline-flex items-center px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl font-semibold text-sm transition-colors shadow-sm active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    เพิ่มกระเป๋า
                </button>
            </div>

            <!-- Empty State -->
            @if($wallets->isEmpty())
                <div class="text-center py-14 bg-card rounded-2xl border border-border">
                    <div class="w-14 h-14 rounded-2xl bg-surface-subtle flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 003-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-[16px] font-semibold text-foreground mb-2">ยังไม่มีกระเป๋าเงิน</h3>
                    <p class="text-text-muted text-sm mb-4">เริ่มต้นด้วยการเพิ่มกระเป๋าเงินแรกของคุณ</p>
                </div>
            @else
                <!-- Wallets Grid -->
                <div x-data="walletReorder()" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" x-ref="grid">
                    @foreach($wallets as $wallet)
                        @php
                            $typeMeta = match($wallet->type->value) {
                                'bank' => [
                                    'label' => 'ธนาคาร',
                                    'color' => '#007AFF',
                                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18" /></svg>'
                                ],
                                'ewallet' => [
                                    'label' => 'e-Wallet',
                                    'color' => '#5856D6',
                                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>'
                                ],
                                'cash' => [
                                    'label' => 'เงินสด',
                                    'color' => '#34C759',
                                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                                ],
                            };
                        @endphp
                        <div class="bg-card rounded-2xl border border-border p-4 flex flex-col shadow-card hover:shadow-elevated transition-shadow" data-wallet-id="{{ $wallet->id }}">
                            <!-- Wallet Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 rounded-xl grid place-items-center text-white shrink-0" style="background-color: {{ $typeMeta['color'] }}">
                                        {!! $typeMeta['icon'] !!}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[14px]">{{ $wallet->name }}</div>
                                        <div class="text-xs text-text-muted">
                                            {{ $typeMeta['label'] }}
                                            @if($wallet->bank_name)
                                                · {{ $wallet->bank_name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button data-drag-handle class="p-1.5 text-text-muted hover:text-foreground hover:bg-surface-subtle rounded-lg transition-colors cursor-grab active:cursor-grabbing" x-tooltip="'ลากเพื่อจัดเรียง'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Balance -->
                            <div class="mb-3">
                                <div class="text-[11px] text-text-muted font-medium">ยอดคงเหลือ</div>
                                <div class="text-[22px] font-bold tracking-tight">{{ number_format($wallet->balance, 2) }} บาท</div>
                            </div>

                            <!-- Recent Transactions -->
                            <ul class="space-y-1.5 text-sm flex-1 mb-3">
                                @if($wallet->transactions->isNotEmpty())
                                    @foreach($wallet->transactions->take(3) as $transaction)
                                        <li class="flex items-center justify-between gap-2">
                                            <span class="truncate text-text-muted text-[13px]">{{ $transaction->recipient ?? $transaction->category?->name ?? '-' }}</span>
                                            <span class="font-semibold shrink-0 text-[13px] {{ $transaction->isExpense() ? 'text-destructive' : 'text-success' }}">
                                                {{ $transaction->isExpense() ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-text-muted text-xs">ยังไม่มีรายการ</li>
                                @endif
                                @if($wallet->transactions->first())
                                    <li class="text-[11px] text-text-muted">
                                        ล่าสุด: {{ $wallet->transactions->first()->transacted_at?->diffForHumans() ?? '-' }}
                                    </li>
                                @endif
                            </ul>

                            <!-- Action Button -->
                            @if($wallet->isOwner($user) || !$isFree)
                                <a href="{{ route('wallets.show', $wallet) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-sm font-medium text-foreground mt-auto">
                                    ดูทั้งหมด / ปรับยอด
                                </a>
                            @else
                                <button @click="$paywall?.open()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-sm font-medium text-foreground mt-auto">
                                    ดูทั้งหมด / ปรับยอด
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Create Wallet Modal -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;">
                <!-- Backdrop -->
                <div x-show="open" x-transition:enter="transition ease-out duration-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>

                <!-- Modal Content -->
                <form id="wallet-create-form" x-show="open" x-transition:enter="transition ease-out duration-350" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-250" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" method="POST" action="{{ route('wallets.store') }}" class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full max-w-md overflow-hidden flex flex-col">
                    @csrf
                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
                        <h3 class="text-[17px] font-semibold text-foreground">เพิ่มกระเป๋าใหม่</h3>
                        <button type="button" @click="open = false" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-subtle active:bg-surface-elevated transition-colors" aria-label="ปิด">
                            <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 min-h-0 overflow-y-auto px-6 pt-5 pb-6">
                        <div class="space-y-3.5">
                            <div>
                                <label for="wname" class="block text-sm font-semibold text-foreground mb-1.5">ชื่อกระเป๋า</label>
                                <input type="text" id="wname" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                @error('name')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-foreground mb-1.5">ประเภท</label>
                                <select id="type" name="type" x-model="draft.type" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors">
                                    <option value="bank">ธนาคาร</option>
                                    <option value="ewallet">e-Wallet</option>
                                    <option value="cash">เงินสด</option>
                                </select>
                                @error('type')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-foreground mb-1.5">การเข้าถึง</label>
                                <select id="access_type" name="access_type" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors">
                                    @if($isFree)
                                        <option value="personal" selected>ส่วนตัว</option>
                                    @else
                                        <option value="personal">ส่วนตัว</option>
                                        <option value="shared">แชร์กับเพื่อน</option>
                                    @endif
                                </select>
                                @if($isFree)
                                    <p class="text-xs text-text-muted mt-1">สมัครสมาชิก Premium เพื่อสร้างกระเป๋าแชร์</p>
                                @endif
                                @error('access_type')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <template x-if="draft.type === 'bank'">
                                <div class="space-y-3.5">
                                    <div>
                                        <label for="bank" class="block text-sm font-semibold text-foreground mb-1.5">ชื่อธนาคาร</label>
                                        <input type="text" id="bank" name="bank_name" placeholder="SCB, KBank, BBL..." class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                        @error('bank_name')
                                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="account" class="block text-sm font-semibold text-foreground mb-1.5">เลขที่บัญชี</label>
                                        <input type="text" id="account" name="account_number" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                        @error('account_number')
                                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </template>
                            <div>
                                <label for="bal" class="block text-sm font-semibold text-foreground mb-1.5">ยอดเริ่มต้น</label>
                                <input type="number" id="bal" name="opening_balance" step="0.01" min="0" value="0" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors" />
                                @error('opening_balance')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-semibold text-foreground mb-1.5">หมายเหตุ</label>
                                <textarea id="notes" name="notes" rows="2" placeholder="เพิ่มบันทึกเกี่ยวกับกระเป๋าเงินนี้" class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors resize-none"></textarea>
                                @error('notes')
                                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" id="is_default" name="is_default" value="1" class="w-4 h-4 rounded border-border text-primary focus:ring-ring" @checked(old('is_default')) />
                                <label for="is_default" class="text-sm font-medium text-foreground">ตั้งเป็นกระเป๋าหลัก</label>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 p-5 border-t border-border bg-surface-subtle shrink-0">
                        <button type="button" @click="open = false" class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm">
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
</x-app-layout>