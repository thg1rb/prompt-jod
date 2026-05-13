@props([
    'show' => false,
])

@php
$user = auth()->user();
$subscription = $user?->subscription;
$shouldShow = $show && $user && (! $subscription || $subscription->status === \App\Enums\SubscriptionStatus::Expired);
@endphp

@if($shouldShow)
<div
    x-data="subscriptionModal()"
    x-init="init()"
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    <div class="fixed inset-0 bg-black/60" aria-hidden="true"></div>

    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        class="relative bg-card border border-border rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10"
    >
        <div class="p-6 sm:p-8">
            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-foreground">เลือกแพ็กเกจของคุณ</h2>
                <p class="text-muted-foreground mt-2">ปลดล็อกฟีเจอร์ทั้งหมดของ PromptJod Premium</p>
            </div>

            <div class="bg-primary/5 border border-primary/10 rounded-xl p-4 mb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-primary/70 mb-3 text-center">สิทธิประโยชน์ทั้งหมด</p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">กระเป๋าเงินไม่จำกัด</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">ธุรกรรมไม่จำกัด</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">OCR สลิปอัตโนมัติ</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">จัดหมวดหมู่อัตโนมัติ</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">งบประมาณและแจ้งเตือน</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">รายงานขั้นสูง</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">ส่งออก CSV/PDF</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-foreground">สำรองข้อมูลอัตโนมัติ</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <button
                    @click="selectPlan('monthly')"
                    :class="selectedPlan === 'monthly' ? 'ring-2 ring-primary border-primary bg-primary/5' : 'border-border hover:border-primary/50'"
                    class="text-center py-4 px-3 rounded-xl border-2 transition-all duration-200"
                >
                    <div class="font-semibold text-foreground text-sm mb-1">รายเดือน</div>
                    <div class="flex items-baseline justify-center gap-0.5">
                        <span class="text-2xl font-bold text-foreground">฿99</span>
                        <span class="text-xs text-muted-foreground">/เดือน</span>
                    </div>
                    <div x-show="selectedPlan === 'monthly'" class="mt-2 flex items-center justify-center" x-transition>
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-primary">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            เลือกแล้ว
                        </span>
                    </div>
                </button>

                <button
                    @click="selectPlan('yearly')"
                    :class="selectedPlan === 'yearly' ? 'ring-2 ring-primary border-primary bg-primary/5' : 'border-border hover:border-primary/50'"
                    class="relative text-center py-4 px-3 rounded-xl border-2 transition-all duration-200"
                >
                    <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 bg-primary text-primary-foreground text-[10px] font-bold rounded-full uppercase tracking-wide">
                        ประหยัด 16%
                    </span>
                    <div class="font-semibold text-foreground text-sm mb-1">รายปี</div>
                    <div class="flex items-baseline justify-center gap-0.5">
                        <span class="text-2xl font-bold text-foreground">฿999</span>
                        <span class="text-xs text-muted-foreground">/ปี</span>
                    </div>
                    <div x-show="selectedPlan === 'yearly'" class="mt-2 flex items-center justify-center" x-transition>
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-primary">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            เลือกแล้ว
                        </span>
                    </div>
                </button>
            </div>

            <button
                @click="pay()"
                :disabled="!selectedPlan || isPaying"
                class="w-full py-3.5 rounded-xl font-semibold text-base transition-all duration-200"
                :class="selectedPlan && !isPaying ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'bg-muted text-muted-foreground cursor-not-allowed'"
            >
                <span x-show="!isPaying">ชำระเงิน</span>
                <span x-show="isPaying" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    กำลังดำเนินการ...
                </span>
            </button>

            <div x-show="error" class="mt-4 p-3 bg-destructive/10 border border-destructive/20 rounded-lg">
                <p class="text-destructive text-sm" x-text="error"></p>
            </div>

            <p class="text-xs text-muted-foreground text-center mt-4">
                ข้อมูลบัตรเครดิตจะถูกเข้ารหัสโดย Omise ผู้ให้บริการชำระเงิน
            </p>
        </div>
    </div>
</div>
@endif
