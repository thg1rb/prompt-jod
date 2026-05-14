<x-app-layout>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-5">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-foreground transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            กลับ
        </a>

        <h1 class="text-[22px] font-bold tracking-tight">นโยบายความเป็นส่วนตัว</h1>

        <div class="space-y-4">
            <div class="bg-card rounded-2xl border border-border p-5 shadow-card">
                <h2 class="text-[16px] font-semibold text-foreground mb-2">1. ข้อมูลที่เราเก็บรวบรวม</h2>
                <p class="text-sm text-text-muted leading-relaxed">
                    เพื่อให้ PromptJod ทำงานได้อย่างถูกต้อง เราอาจเก็บรวบรวมข้อมูลต่อไปนี้:
                </p>
                <ul class="list-disc list-inside text-text-muted mt-2 space-y-1 text-sm">
                    <li>ข้อมูลบัญชีผู้ใช้ (ชื่อ, อีเมล)</li>
                    <li>ข้อมูลกระเป๋าเงินและธุรกรรม</li>
                    <li>รูปภาพสลิปที่อัปโหลด (สำหรับ OCR)</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>