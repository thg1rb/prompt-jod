@props([
    'wallets',
    'categories',
    'initialOpen' => false,
    'currentUserId' => null,
    'isPremium' => true,
])

<div
    x-data="transactionModal({{
        json_encode([
            'wallets' => $wallets->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'is_default' => $w->is_default, 'type' => $w->type->value, 'access_type' => $w->access_type->value])->values(),
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'icon' => $c->icon])->values(),
            'initialOpen' => $initialOpen,
            'currentUserId' => $currentUserId,
            'isPremium' => $isPremium,
        ])
    }})"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
    style="display: none;"
    @keydown.escape.window="closeModal()"
    @open-transaction-modal.window="openModal()"
    @open-transaction-modal-view.window="openView($event.detail)"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm"
        @click="closeModal()"
    ></div>

    <!-- Modal Content - Bottom sheet on mobile, centered on desktop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-350"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-250"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
        class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full sm:max-w-2xl sm:max-h-[88vh] overflow-hidden flex flex-col"
        @click.stop
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
            <h3 class="text-[17px] font-semibold text-foreground" x-text="title"></h3>
            <button
                @click="closeModal()"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-subtle active:bg-surface-elevated transition-colors"
                aria-label="ปิด"
            >
                <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-5">
            <!-- Slip Upload Section (Create Mode Only, Premium Only) -->
            <template x-if="isCreateMode && isPremium">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-foreground">อัพโหลดสลิป (อัตโนมัติกรอกข้อมูล)</label>

                    <!-- Dropzone -->
                    <div
                        @dragover.prevent
                        @drop.prevent="handleDrop($event)"
                        class="relative border-2 border-dashed border-border rounded-2xl p-5 text-center hover:border-primary/50 transition-colors cursor-pointer"
                        :class="verifying ? 'opacity-50 pointer-events-none' : ''"
                    >
                        <label
                            :class="slipImagePreview ? 'hidden' : 'block cursor-pointer w-full h-full'"
                            x-show="!slipImagePreview"
                        >
                            <input
                                x-ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg"
                                @change="handleFileUpload($event)"
                                class="hidden"
                            >
                            <div class="space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 mx-auto flex items-center justify-center">
                                    <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-text-muted">
                                    ลากไฟล์มาวางที่นี่ หรือ
                                    <span class="text-primary hover:text-primary/80 font-medium">คลิกเพื่อเลือกไฟล์</span>
                                </p>
                                <p class="text-xs text-text-muted">JPEG, PNG, JPG (สูงสุด 5MB)</p>
                            </div>
                        </label>

                        <template x-if="slipImagePreview">
                            <div class="relative inline-block" @click.stop>
                                <img :src="slipImagePreview" alt="Slip preview" class="max-h-40 rounded-2xl mx-auto">
                                <button
                                    @click.prevent="slipImagePreview = null; slipData = null; slipError = null; $refs.fileInput.value = '';"
                                    class="absolute -top-2 -right-2 bg-destructive text-destructive-foreground rounded-full p-1.5 hover:bg-destructive/90 transition-colors shadow-sm"
                                    aria-label="ลบรูป"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Loading State -->
                        <div x-show="verifying" class="mt-3">
                            <svg class="animate-spin h-5 w-5 text-primary mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-xs text-text-muted mt-1">กำลังอ่านข้อมูล...</p>
                        </div>

                        <!-- Error State -->
                        <div x-show="slipError" class="mt-3 p-3 bg-destructive-light border border-destructive/20 rounded-xl">
                            <p class="text-sm text-destructive" x-text="slipError"></p>
                        </div>

                        <!-- Success State -->
                        <div x-show="slipData && !slipError" class="mt-3 p-3 bg-success-light border border-success/20 rounded-xl">
                            <p class="text-sm text-success font-medium">✓ อ่านข้อมูลสลิปสำเร็จ ข้อมูลถูกกรอกอัตโนมัติ</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Free User Slip Upload Notice -->
            <template x-if="isCreateMode && !isPremium">
                <div class="p-4 border border-border rounded-2xl bg-surface-subtle">
                    <p class="text-sm text-text-muted text-center">
                        ระบบไม่รองรับการอัพโหลดสลิปสำหรับผู้ใช้ฟรี
                        <button @click="$paywall?.open()" class="text-primary hover:underline font-medium">สมัครสมาชิก Premium</button>
                    </p>
                </div>
            </template>

            <!-- Form Error Message -->
            <div x-show="errors._form" class="p-3 bg-destructive-light border border-destructive/20 rounded-xl">
                <p class="text-sm text-destructive" x-text="errors._form"></p>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4">
                <!-- Transaction Type -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">ประเภทธุรกรรม</label>
                    <div class="flex gap-2.5 rounded-xl overflow-hidden border border-border" :class="isViewMode ? 'pointer-events-none opacity-60' : ''">
                        <button
                            type="button"
                            @click="form.type = 'expense'"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="form.type === 'expense' ? 'bg-destructive text-destructive-foreground' : 'bg-card text-text-muted hover:bg-surface-subtle'"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            รายจ่าย
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'income'"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="form.type === 'income' ? 'bg-success text-success-foreground' : 'bg-card text-text-muted hover:bg-surface-subtle'"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                            รายรับ
                        </button>
                    </div>
                </div>

                <!-- Wallet & Amount -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="wallet" class="block text-sm font-medium text-foreground mb-1.5">กระเป๋าเงิน <span class="text-destructive">*</span></label>
                        <select
                            id="wallet"
                            x-model="form.wallet_id"
                            @change="clearError('wallet_id')"
                            :disabled="isViewMode"
                            class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            :class="errors.wallet_id ? 'border-destructive' : ''"
                            required
                        >
                            <option value="">เลือกกระเป๋า</option>
                            <template x-for="wallet in wallets" :key="wallet.id">
                                <option :value="wallet.id" x-text="wallet.name"></option>
                            </template>
                        </select>
                        <p x-show="errors.wallet_id" class="mt-1 text-xs text-destructive" x-text="errors.wallet_id"></p>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-foreground mb-1.5">จำนวนเงิน (THB) <span class="text-destructive">*</span></label>
                        <input
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            x-model="form.amount"
                            @input="clearError('amount')"
                            :disabled="isViewMode"
                            class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            :class="errors.amount ? 'border-destructive' : ''"
                            placeholder="0.00"
                            required
                        >
                        <p x-show="errors.amount" class="mt-1 text-xs text-destructive" x-text="errors.amount"></p>
                    </div>
                </div>

                <!-- Date Time -->
                <div>
                    <label for="transacted_at" class="block text-sm font-medium text-foreground mb-1.5">วันเวลาทำรายการ <span class="text-destructive">*</span></label>
                    <input
                        id="transacted_at"
                        type="datetime-local"
                        x-model="form.transacted_at"
                        @input="clearError('transacted_at')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        :class="errors.transacted_at ? 'border-destructive' : ''"
                        required
                    >
                    <p x-show="errors.transacted_at" class="mt-1 text-xs text-destructive" x-text="errors.transacted_at"></p>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-foreground mb-1.5">หมวดหมู่ <span class="text-destructive">*</span></label>
                    <select
                        id="category"
                        x-model="form.category_id"
                        @change="clearError('category_id')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        :class="errors.category_id ? 'border-destructive' : ''"
                        required
                    >
                        <option value="">เลือกหมวดหมู่</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.icon + ' ' + category.name"></option>
                        </template>
                    </select>
                    <p x-show="errors.category_id" class="mt-1 text-xs text-destructive" x-text="errors.category_id"></p>
                </div>

                 <!-- Sender -->
                 <div class="relative" @click.away="hideSuggestions()">
                     <label for="sender" class="block text-sm font-medium text-foreground mb-1.5"><span x-text="senderLabel"></span> <span class="text-destructive">*</span></label>
                     <input
                         id="sender"
                         type="text"
                         x-model="form.sender"
                         @input="onSenderInput()"
                         @focus="onSenderFocus()"
                         @keydown="onSenderKeydown($event)"
                         :disabled="isViewMode"
                         class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                         :class="errors.sender ? 'border-destructive' : ''"
                         :placeholder="isCashWallet ? 'ชื่อผู้จ่าย' : 'ชื่อผู้โอน'"
                         required
                         autocomplete="off"
                     >
                     <p x-show="errors.sender" class="mt-1 text-xs text-destructive" x-text="errors.sender"></p>

                     <!-- Sender Suggestions Dropdown -->
                     <div
                         x-show="showSenderSuggestions && !isViewMode"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-10 w-full mt-1 bg-popover border border-border rounded-xl shadow-floating py-1 max-h-60 overflow-y-auto"
                         style="display: none;"
                     >
                         <template x-for="(name, index) in senderSuggestions" :key="name">
                             <button
                                 type="button"
                                 @click.prevent="selectSenderSuggestion(name)"
                                 @mouseenter="senderHighlightedIndex = index"
                                 class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors text-foreground"
                                 :class="index === senderHighlightedIndex ? 'bg-surface-subtle' : ''"
                                 x-text="name"
                             ></button>
                         </template>
                         <div x-show="senderSuggestions.length === 0" class="px-3 py-2.5 text-sm text-text-muted">
                             ไม่พบข้อมูล
                         </div>
                     </div>
                 </div>

                <!-- Recipient -->
                <div class="relative" @click.away="hideSuggestions()">
                    <label for="recipient" class="block text-sm font-medium text-foreground mb-1.5">ผู้รับ <span class="text-destructive">*</span></label>
                    <input
                        id="recipient"
                        type="text"
                        x-model="form.recipient"
                        @input="onRecipientInput()"
                        @focus="onRecipientFocus()"
                        @keydown="onRecipientKeydown($event)"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        :class="errors.recipient ? 'border-destructive' : ''"
                        placeholder="ชื่อผู้รับ"
                        required
                        autocomplete="off"
                    >
                    <p x-show="errors.recipient" class="mt-1 text-xs text-destructive" x-text="errors.recipient"></p>

                    <!-- Recipient Suggestions Dropdown -->
                    <div
                        x-show="showRecipientSuggestions && !isViewMode"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-10 w-full mt-1 bg-popover border border-border rounded-xl shadow-floating py-1 max-h-60 overflow-y-auto"
                        style="display: none;"
                    >
                        <template x-for="(name, index) in recipientSuggestions" :key="name">
                            <button
                                type="button"
                                @click.prevent="selectRecipientSuggestion(name)"
                                @mouseenter="recipientHighlightedIndex = index"
                                class="w-full px-3 py-2.5 text-left text-sm hover:bg-surface-subtle transition-colors text-foreground"
                                :class="index === recipientHighlightedIndex ? 'bg-surface-subtle' : ''"
                                x-text="name"
                            ></button>
                        </template>
                        <div x-show="recipientSuggestions.length === 0" class="px-3 py-2.5 text-sm text-text-muted">
                            ไม่พบข้อมูล
                        </div>
                    </div>
                </div>

                 <!-- Transaction Reference -->
                 <div x-show="!isCashWallet" style="display: none;">
                     <label for="transaction_ref" class="block text-sm font-medium text-foreground mb-1.5">เลขอ้างอิง</label>
                     <input
                         id="transaction_ref"
                         type="text"
                         x-model="form.transaction_ref"
                         :disabled="isViewMode"
                         class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                         placeholder="เลขอ้างอิงธุรกรรม"
                         x-show="isCreateMode"
                         style="display: none;"
                     >
                     <div
                         x-show="!isCreateMode"
                         class="w-full px-3 py-2.5 rounded-xl border border-border bg-surface-subtle text-text-muted text-sm"
                         style="display: none;"
                     >
                         <span x-text="form.transaction_ref || '-'"></span>
                     </div>
                 </div>

                <!-- Note -->
                <div>
                    <label for="note" class="block text-sm font-medium text-foreground mb-1.5">บันทึกย่อ</label>
                    <textarea
                        id="note"
                        rows="3"
                        x-model="form.note"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors resize-none disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        placeholder="บันทึกเพิ่มเติม..."
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div
            x-show="isCreateMode || isEditMode || (isViewMode && isOwner)"
            class="flex gap-3 p-5 border-t border-border bg-surface-subtle shrink-0"
        >
            <!-- Create Mode Footer -->
            <template x-if="isCreateMode">
                <div class="flex gap-3 w-full">
                    <button
                        @click="closeModal()"
                        class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm"
                        :disabled="loading"
                    >
                        ยกเลิก
                    </button>
                    <button
                        @click="submit()"
                        class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        :disabled="loading"
                    >
                        <template x-if="loading">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                กำลังบันทึก...
                            </span>
                        </template>
                        <template x-if="!loading">
                            <span x-text="submitText"></span>
                        </template>
                    </button>
                </div>
            </template>

            <!-- View Mode Footer -->
            <template x-if="isViewMode && isOwner">
                <div class="flex gap-3 w-full">
                    <button
                        @click="toggleEditMode()"
                        class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm shadow-sm"
                    >
                        แก้ไข
                    </button>
                    <button
                        @click="delete()"
                        class="px-4 py-2.5 bg-destructive hover:bg-destructive/90 text-destructive-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        :disabled="loading"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>

            <!-- Edit Mode Footer -->
            <template x-if="isEditMode">
                <div class="flex gap-3 w-full">
                    <button
                        @click="toggleEditMode()"
                        class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm"
                    >
                        ยกเลิก
                    </button>
                    <button
                        @click="submit()"
                        class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        :disabled="loading"
                    >
                        <template x-if="loading">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                กำลังบันทึก...
                            </span>
                        </template>
                        <template x-if="!loading">
                            <span x-text="submitText"></span>
                        </template>
                    </button>
                    <button
                        @click="delete()"
                        class="px-4 py-2.5 bg-destructive hover:bg-destructive/90 text-destructive-foreground rounded-xl transition-colors font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        :disabled="loading"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>