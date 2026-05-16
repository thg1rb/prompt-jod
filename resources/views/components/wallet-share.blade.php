@props([
    'walletId',
    'isOwner' => false,
])

<div
    x-data="walletShare('{{ $walletId }}', {{ $isOwner ? 'true' : 'false' }})"
    x-show="shareOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
    style="display: none;"
    @keydown.escape.window="closeShareModal()"
    @open-share-modal.window="openShareModal()"
>
    <!-- Backdrop -->
    <div
        x-show="shareOpen"
        x-transition:enter="transition ease-out duration-75"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm"
        @click="closeShareModal()"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="shareOpen"
        x-transition:enter="transition ease-out duration-350"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-250"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full max-h-[70vh] sm:max-w-lg sm:max-h-[88vh] overflow-hidden flex flex-col"
        @click.stop
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
            <h3 class="text-[17px] font-semibold text-foreground">แชร์กระเป๋าเงิน</h3>
            <button
                @click="closeShareModal()"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-subtle active:bg-surface-elevated transition-colors"
                aria-label="ปิด"
            >
                <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-border px-5" x-show="isOwner">
            <nav class="-mb-px flex gap-5" aria-label="Tabs">
                <button
                    @click="activeTab = 'members'"
                    :class="activeTab === 'members' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-foreground'"
                    class="whitespace-nowrap py-3 px-0.5 border-b-2 font-semibold text-sm transition-colors"
                >
                    สมาชิก
                </button>
                <button
                    @click="activeTab = 'invite'"
                    :class="activeTab === 'invite' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-foreground'"
                    class="whitespace-nowrap py-3 px-0.5 border-b-2 font-semibold text-sm transition-colors"
                >
                    เชิญเพื่อน
                </button>
                <button
                    @click="activeTab = 'email'"
                    :class="activeTab === 'email' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-foreground'"
                    class="whitespace-nowrap py-3 px-0.5 border-b-2 font-semibold text-sm transition-colors"
                >
                    ส่งอีเมล
                </button>
            </nav>
        </div>

        <!-- Body -->
        <div class="p-5 flex-1 min-h-0 overflow-y-auto">
            <!-- Members Tab -->
            <div x-show="activeTab === 'members'">
                <div x-show="loading" class="text-center py-4 text-text-muted">กำลังโหลด...</div>
                <div x-show="!loading && members.length === 0" class="text-center py-4 text-text-muted">
                    ยังไม่มีสมาชิก
                </div>
                <ul class="divide-y divide-border" x-show="!loading && members.length > 0">
                    <template x-for="member in members" :key="member.id">
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold ring-1 ring-primary/20">
                                    <span x-text="member.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <div class="font-medium text-foreground" x-text="member.name"></div>
                                    <div class="text-xs text-text-muted" x-text="member.email"></div>
                                </div>
                            </div>
                            <button
                                @click="removeMember(member.user_id)"
                                class="text-sm text-destructive hover:text-destructive/80 font-medium transition-colors"
                            >
                                ลบ
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Invite Tab -->
            <div x-show="activeTab === 'invite'">
                <p class="text-sm text-text-muted mb-4">
                    สร้างลิงก์เชิญเพื่อแชร์กระเป๋าเงินนี้กับเพื่อน ลิงก์จะหมดอายุภายใน 24 ชั่วโมง
                </p>

                <button
                    @click="generateInvitation()"
                    :disabled="generating"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 shadow-sm active:scale-[0.98]"
                >
                    <svg x-show="!generating" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="generating ? 'กำลังสร้าง...' : 'สร้างลิงก์เชิญ'"></span>
                </button>

                <div x-show="newInvitationUrl" class="mt-4">
                    <label class="block text-sm font-semibold text-foreground mb-1.5">ลิงก์เชิญ</label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            readonly
                            :value="newInvitationUrl"
                            class="flex-1 px-3 py-2.5 rounded-xl border border-border bg-surface-subtle text-foreground text-sm"
                        >
                        <button
                            @click="copyInvitationUrl()"
                            class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-medium text-sm shadow-sm"
                        >
                            <span x-show="!copied">คัดลอก</span>
                            <span x-show="copied" class="text-primary-foreground">คัดลอกแล้ว!</span>
                        </button>
                    </div>
                </div>

                <div x-show="invitations.length > 0" class="mt-6">
                    <h4 class="text-sm font-semibold text-foreground mb-2">ลิงก์เชิญที่รอดำเนินการ</h4>
                    <ul class="divide-y divide-border">
                        <template x-for="inv in invitations" :key="inv.id">
                            <li class="py-2 flex items-center justify-between text-sm">
                                <div>
                                    <div class="text-foreground" x-text="inv.email"></div>
                                    <div class="text-xs text-text-muted">หมดอายุ: <span x-text="inv.expires_at"></span></div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Email Tab -->
            <div x-show="activeTab === 'email'">
                <p class="text-sm text-text-muted mb-4">
                    ส่งเชิญเข้าร่วมกระเป๋าเงินไปยังอีเมลของเพื่อน ลิงก์จะหมดอายุภายใน 24 ชั่วโมง
                </p>

                <form @submit.prevent="sendInvitationEmail()">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-foreground mb-1.5">อีเมลเพื่อน</label>
                        <input
                            type="email"
                            x-model="emailInput"
                            placeholder="friend@example.com"
                            class="w-full px-3 py-2.5 rounded-xl border border-border bg-surface text-foreground text-sm placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                            :class="{ 'border-destructive': emailError }"
                        >
                        <p x-show="emailError" class="mt-1.5 text-xs text-destructive" x-text="emailError"></p>
                    </div>

                    <button
                        type="submit"
                        :disabled="sendingEmail || !emailInput.trim()"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 shadow-sm active:scale-[0.98]"
                    >
                        <svg x-show="!sendingEmail" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-text="sendingEmail ? 'กำลังส่ง...' : 'ส่งเชิญอีเมล'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>