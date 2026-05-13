<div class="bg-card border border-border rounded-lg p-6"
     x-data="cardForm()"
     data-omise-public-key="{{ config('services.omise.public_key') }}"
     @keydown.escape="$dispatch('close-card-form')">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">ชำระเงินสมาชิก</h3>
        <button @click="$dispatch('close-card-form')" class="text-text-muted hover:text-foreground">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="text-center">
        <div class="mb-6">
            <p class="text-3xl font-bold">฿99.00<span class="text-sm font-normal text-text-muted">/เดือน</span></p>
            <p class="text-sm text-text-muted mt-1">PromptJod Premium</p>
        </div>

        <div x-show="error" class="mb-4 p-3 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive text-sm" x-text="error"></p>
        </div>

        <button id="omise-pay-button"
                @click="openOmiseForm()"
                :disabled="loading"
                class="w-full px-6 py-3 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!loading">ชำระเงิน ฿99.00</span>
            <span x-show="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                กำลังดำเนินการ...
            </span>
        </button>

        <p class="text-xs text-text-muted mt-4">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            ข้อมูลบัตรเครดิตจะถูกเข้ารหัสโดย Omise ผู้ให้บริการชำระเงิน
        </p>
    </div>
</div>
