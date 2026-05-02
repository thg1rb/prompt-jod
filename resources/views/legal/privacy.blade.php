<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center text-muted hover:text-foreground mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            กลับ
        </a>

        <h1 class="text-3xl font-bold text-foreground mb-6">นโยบายความเป็นส่วนตัว</h1>

        <div class="space-y-6">
            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">1. ข้อมูลที่เราเก็บรวบรวม</h2>
                <p class="text-muted leading-relaxed">
                    เพื่อให้ PromptJod ทำงานได้อย่างถูกต้อง เราอาจเก็บรวบรวมข้อมูลต่อไปนี้:
                </p>
                <ul class="list-disc list-inside text-muted mt-2 space-y-1">
                    <li>ข้อมูลบัญชีผู้ใช้ (ชื่อ, อีเมล)</li>
                    <li>ข้อมูลกระเป๋าเงินและบัญชีธนาคาร</li>
                    <li>ข้อมูลรายการรับ-จ่าย</li>
                    <li>รูปภาพสลิปโอนเงินที่อัปโหลด</li>
                    <li>ข้อมูลการใช้งานแอปพลิเคชัน</li>
                </ul>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">2. การใช้งานข้อมูล</h2>
                <p class="text-muted leading-relaxed">
                    เราใช้ข้อมูลของคุณเพื่อวัตถุประสงค์ดังนี้:
                </p>
                <ul class="list-disc list-inside text-muted mt-2 space-y-1">
                    <li>จัดการและบันทึกบัญชีการเงินของคุณ</li>
                    <li>ให้บริการวิเคราะห์การใช้จ่าย</li>
                    <li>ปรับปรุงและพัฒนาฟีเจอร์ใหม่</li>
                    <li>ส่งการแจ้งเตือนและอัปเดตที่เกี่ยวข้อง</li>
                    <li>ป้องกันการฉ้อโกงและการใช้งานที่ผิดกฎหมาย</li>
                </ul>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">3. การแชร์ข้อมูล</h2>
                <p class="text-muted leading-relaxed">
                    เราไม่ขายหรือแชร์ข้อมูลส่วนตัวของคุณให้กับบุคคลที่สาม โดยไม่ได้รับความยินยอมจากคุณ
                    เว้นแต่ในกรณีที่จำเป็นตามกฎหมาย หรือเพื่อปกป้องสิทธิของเรา
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">4. การจัดเก็บข้อมูล</h2>
                <p class="text-muted leading-relaxed">
                    ข้อมูลของคุณจะถูกจัดเก็บบนเซิร์ฟเวอร์ที่ปลอดภัย
                    เราใช้มาตรการรักษาความปลอดภัยที่เหมาะสมเพื่อป้องกันการเข้าถึงโดยไม่ได้รับอนุญาต
                    การสูญหาย หรือการปลอมแปลงข้อมูล
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">5. สิทธิของคุณ</h2>
                <p class="text-muted leading-relaxed">
                    คุณมีสิทธิ์ในการ:
                </p>
                <ul class="list-disc list-inside text-muted mt-2 space-y-1">
                    <li>เข้าถึงและแก้ไขข้อมูลส่วนตัวของคุณ</li>
                    <li>ขอลบข้อมูลบัญชีของคุณ</li>
                    <li>ขอถอนความยินยอมในการใช้ข้อมูล</li>
                    <li>ติดต่อเราเพื่อสอบถามเกี่ยวกับข้อมูลของคุณ</li>
                </ul>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">6. คุกกี้</h2>
                <p class="text-muted leading-relaxed">
                    เราใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของคุณ
                    คุกกี้คือไฟล์ขนาดเล็กที่จัดเก็บบนอุปกรณ์ของคุณ
                    คุณสามารถตั้งค่าเบราว์เซอร์เพื่อปฏิเสธคุกกี้ได้
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">7. การเปลี่ยนแปลงนโยบาย</h2>
                <p class="text-muted leading-relaxed">
                    เราขอสงวนสิทธิ์ในการแก้ไขนโยบายความเป็นส่วนตัวนี้ได้ตลอดเวลา
                    การแก้ไขจะมีผลบังคับใช้ทันทีเมื่อเผยแพร่
                    เราจะแจ้งให้คุณทราบหากมีการเปลี่ยนแปลงสำคัญ
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">8. การติดต่อ</h2>
                <p class="text-muted leading-relaxed">
                    หากคุณมีข้อสงสัยหรือข้อคิดเห็นเกี่ยวกับนโยบายความเป็นส่วนตัว
                    สามารถติดต่อเราได้ผ่านช่องทางที่ระบุในแอปพลิเคชัน
                </p>
            </div>

            <div class="bg-card rounded-xl shadow-sm border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-3">9. บทสรุป</h2>
                <p class="text-muted leading-relaxed">
                    เราให้ความสำคัญกับความเป็นส่วนตัวของข้อมูลคุณเป็นอย่างยิ่ง
                    และจะดำเนินการตามนโยบายนี้อย่างเคร่งครัด
                    การใช้งาน PromptJod ของคุณถือว่าคุณยอมรับนโยบายความเป็นส่วนตัวนี้
                </p>
                <p class="text-sm text-muted mt-2">
                    อัปเดตล่าสุด: มกราคม 2025
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
