<div class="bg-card border border-border rounded-2xl p-6 shadow-card"
     x-data="cardForm()"
     data-omise-public-key="{{ config('services.omise.public_key') }}"
     @keydown.escape="$dispatch('close-card-form')">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-[16px] font-semibold">ชำระเงินสมาชิก</h3>
        <button @click="$dispatch('close-card-form')" class="w-8 h-8 rounded-full hover:bg-surface-subtle active:bg-surface-elevated flex items-center justify-center transition-colors">
            <svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="text-center">
        <div class="mb-5">
            <p class="text-3xl font-bold tracking-tight">฿99.00<span class="text-sm font-normal text-text-muted">/เดือน</span></p>
            <p class="text-sm text-text-muted mt-1">PromptJod Premium</p>
        </div>

        <div x-show="error" class="mb-4 p-3 bg-destructive-light border border-destructive/20 rounded-xl">
            <p class="text-sm text-destructive" x-text="error"></p>
        </div>

        <button id="omise-pay-button"
                @click="openOmiseForm()"
                :disabled="loading"
                class="w-full px-6 py-3 bg-primary text-primary-foreground rounded-xl font-semibold hover:bg-primary-hover active:scale-[0.98] transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!loading">ชำระเงิน ฿99.00</span>
            <span x-show="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                กำลังดำเนินการ...
            </span>
        </button>

        <p class="text-xs text-text-muted mt-4 flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            ข้อมูลบัตรเครดิตจะถูกเข้ารหัสโดย Omise ผู้ให้บริการชำระเงิน
        </p>
    </div>
</div>