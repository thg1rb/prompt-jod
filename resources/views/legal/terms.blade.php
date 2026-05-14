<x-app-layout>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-5">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-foreground transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            กลับ
        </a>

        <h1 class="text-[22px] font-bold tracking-tight">เงื่อนไขการใช้งาน</h1>

        <div class="space-y-4">
            <div class="bg-card rounded-2xl border border-border p-5 shadow-card">
                <h2 class="text-[16px] font-semibold text-foreground mb-2">1. การยอมรับเงื่อนไข</h2>
                <p class="text-sm text-text-muted leading-relaxed">
                    การเข้าใช้งานแอปพลิเคชัน PromptJod ของคุณ ถือว่าคุณได้อ่านและยอมรับเงื่อนไขการใช้งานเหล่านี้
                    หากคุณไม่ยอมรับเงื่อนไขเหล่านี้ กรุณาอย่าใช้งานแอปพลิเคชัน
                </p>
            </div>

            <div class="bg-card rounded-2xl border border-border p-5 shadow-card">
                <h2 class="text-[16px] font-semibold text-foreground mb-2">2. การใช้งานที่อนุญาต</h2>
                <p class="text-sm text-text-muted leading-relaxed">
                    คุณสามารถใช้งาน PromptJod เพื่อจัดการบัญชีการเงิน บันทึกรายรับ-รายจ่าย และวิเคราะห์การใช้จ่ายได้
                    ทั้งนี้ คุณต้องใช้งานในวัตถุประสงค์ที่ถูกต้องตามกฎหมาย และไม่กระทบต่อสิทธิของผู้อื่น
                </p>
            </div>
        </div>
    </div>
</x-app-layout>