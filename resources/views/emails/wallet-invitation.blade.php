<x-mail::message>
<div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
    </div>
    <h1 class="text-2xl font-bold text-foreground mb-2">เชิญเข้าร่วมกระเป๋าเงิน</h1>
    <p class="text-text-muted">{{ $ownerName }} เชิญคุณเข้าร่วมกระเป๋าเงิน <strong class="text-foreground">{{ $walletName }}</strong></p>
</div>

<div class="bg-secondary rounded-xl p-6 mb-6">
    <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
            {{ strtoupper(substr($ownerName, 0, 1)) }}
        </div>
        <div>
            <div class="font-semibold text-foreground">{{ $ownerName }}</div>
            <div class="text-sm text-text-muted">เจ้าของกระเป๋าเงิน</div>
        </div>
    </div>
    <div class="text-sm text-text-secondary">
        <span class="font-semibold text-foreground">{{ $walletName }}</span>
    </div>
</div>

<div class="text-center mb-6">
    <p class="text-sm text-text-muted mb-4">คลิกปุ่มด้านล่างเพื่อเข้าร่วมกระเป๋าเงิน<br>
    <span class="text-xs">ลิงก์จะหมดอายุในวันที่ {{ $expiresAt }}</span></p>
    <x-mail::button :url="$acceptUrl" color="primary">
        เข้าร่วมกระเป๋าเงิน
    </x-mail::button>
</div>

<div class="text-center text-xs text-text-muted">
    <p>หากคุณไม่ต้องการเข้าร่วม สามารถเพิกเฉยต่ออีเมลฉบับนี้ได้</p>
</div>

<x-mail::subcopy>
    หากปุ่มไม่ทำงาน คัดลอกลิงก์ด้านล่างแล้ววางในเบราว์เซอร์ของคุณ:
    <br>
    <a class="text-primary break-all">{{ $acceptUrl }}</a>
</x-mail::subcopy>

<div class="mt-8 pt-6 border-t border-border text-center">
    <p class="text-xs text-text-muted">
        PromptJod — แอปจัดการการเงินส่วนบุคคล
    </p>
</div>
</x-mail::message>