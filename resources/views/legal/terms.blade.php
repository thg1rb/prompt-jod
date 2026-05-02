<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center text-muted hover:text-foreground mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            กลับ
        </a>

        <h1 class="text-3xl font-bold text-foreground mb-6">เงื่อนไขการใช้งาน</h1>

        <div class="space-y-6">
            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">1. การยอมรับเงื่อนไข</h2>
                <p class="text-muted leading-relaxed">
                    การเข้าใช้งานแอปพลิเคชัน PromptJod ของคุณ ถือว่าคุณได้อ่านและยอมรับเงื่อนไขการใช้งานเหล่านี้
                    หากคุณไม่ยอมรับเงื่อนไขเหล่านี้ กรุณาอย่าใช้งานแอปพลิเคชัน
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">2. การใช้งานที่อนุญาต</h2>
                <p class="text-muted leading-relaxed">
                    คุณสามารถใช้งาน PromptJod เพื่อจัดการบัญชีการเงิน บันทึกรายรับ-รายจ่าย และวิเคราะห์การใช้จ่ายได้
                    ทั้งนี้ คุณต้องใช้งานในวัตถุประสงค์ที่ถูกต้องตามกฎหมาย และไม่กระทบต่อสิทธิของผู้อื่น
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">3. ข้อจำกัดความรับผิดชอบ</h2>
                <p class="text-muted leading-relaxed">
                    PromptJod จัดทำขึ้นเพื่อวัตถุประสงค์ในการช่วยจัดการบัญชีการเงินส่วนบุคคล
                    เราไม่รับประกันความถูกต้อง ความสมบูรณ์ หรือความเป็นปัจจุบันของข้อมูลในแอปพลิเคชัน
                    การตัดสินใจทางการเงินทั้งหมดเป็นความรับผิดชอบของคุณเอง
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">4. ความเป็นส่วนตัวของข้อมูล</h2>
                <p class="text-muted leading-relaxed">
                    เราเคารพความเป็นส่วนตัวของข้อมูลคุณ และดำเนินการตามนโยบายความเป็นส่วนตัว
                    สำหรับรายละเอียดเพิ่มเติม โปรดอ่านนโยบายความเป็นส่วนตัวของเรา
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">5. การเปลี่ยนแปลงเงื่อนไข</h2>
                <p class="text-muted leading-relaxed">
                    เราขอสงวนสิทธิ์ในการแก้ไขเงื่อนไขการใช้งานเหล่านี้ได้ตลอดเวลา
                    การแก้ไขจะมีผลบังคับใช้ทันทีเมื่อเผยแพร่บนแอปพลิเคชัน
                    คุณควรตรวจสอบเงื่อนไขเหล่านี้เป็นประจำ
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">6. การติดต่อ</h2>
                <p class="text-muted leading-relaxed">
                    หากคุณมีข้อสงสัยเกี่ยวกับเงื่อนไขการใช้งาน หรือต้องการติดต่อเรา
                    สามารถติดต่อผ่านช่องทางที่ระบุในแอปพลิเคชันได้
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">7. บทสรุป</h2>
                <p class="text-muted leading-relaxed">
                    การใช้งาน PromptJod ของคุณถือว่าคุณได้อ่านและยอมรับเงื่อนไขการใช้งานทั้งหมดนี้
                    หากคุณไม่ยอมรับ กรุณาหยุดใช้งานแอปพลิเคชันทันที
                </p>
                <p class="text-sm text-muted mt-2">
                    อัปเดตล่าสุด: มกราคม 2025
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
