@props([
    'wallets',
    'categories',
    'initialOpen' => false,
])

<div
    x-data="transactionModal({{
        json_encode([
            'wallets' => $wallets->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'is_default' => $w->is_default])->values(),
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'icon' => $c->icon])->values(),
            'initialOpen' => $initialOpen,
        ])
    }})"
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
    @keydown.escape.window="closeModal()"
    @open-transaction-modal.window="openModal()"
    @open-transaction-modal-view.window="openView($event.detail)"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50"
        @click="closeModal()"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-card rounded-xl shadow-lg border border-border w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.stop
    >
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground" x-text="title"></h3>
            <button
                @click="closeModal()"
                class="inline-flex items-center justify-center p-1.5 hover:bg-muted rounded-lg transition-colors"
                aria-label="ปิด"
            >
                <svg class="h-5 w-5 text-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6">
            <!-- Slip Upload Section (Create Mode Only) -->
            <div class="space-y-3" x-show="isCreateMode" style="display: none;">
                <label class="block text-sm font-medium text-foreground">อัพโหลดสลิป (อัตโนมัติกรอกข้อมูล)</label>

                <!-- Dropzone -->
                <div
                    @dragover.prevent
                    @drop.prevent="handleDrop($event)"
                    class="relative border-2 border-dashed border-border rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer"
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
                            <svg class="h-10 w-10 mx-auto text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm text-text-muted">
                                ลากไฟล์มาวางที่นี่ หรือ
                                <span class="text-primary hover:text-primary/80">คลิกเพื่อเลือกไฟล์</span>
                            </p>
                            <p class="text-xs text-text-muted">JPEG, PNG, JPG (สูงสุด 5MB)</p>
                        </div>
                    </label>

                    <template x-if="slipImagePreview">
                        <div class="relative inline-block" @click.stop>
                            <img :src="slipImagePreview" alt="Slip preview" class="max-h-48 rounded-lg mx-auto">
                            <button
                                @click.prevent="slipImagePreview = null; slipData = null; slipError = null; $refs.fileInput.value = '';"
                                class="absolute -top-2 -right-2 bg-destructive text-destructive-foreground rounded-full p-1 hover:bg-destructive/90 transition-colors"
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
                    <div x-show="slipError" class="mt-3 p-3 bg-destructive/10 border border-destructive/20 rounded-lg">
                        <p class="text-sm text-destructive" x-text="slipError"></p>
                    </div>

                    <!-- Success State -->
                    <div x-show="slipData && !slipError" class="mt-3 p-3 bg-success/10 border border-success/20 rounded-lg">
                        <p class="text-sm text-success">✓ อ่านข้อมูลสลิปสำเร็จ ข้อมูลถูกกรอกอัตโนมัติ</p>
                    </div>
                </div>
            </div>

            <!-- Form Error Message -->
            <div x-show="errors._form" class="p-3 bg-destructive/10 border border-destructive/20 rounded-lg">
                <p class="text-sm text-destructive" x-text="errors._form"></p>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4">
                <!-- Transaction Type -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">ประเภทธุรกรรม</label>
                    <div class="flex gap-3" :class="isViewMode ? 'pointer-events-none opacity-70' : ''">
                        <button
                            type="button"
                            @click="form.type = 'expense'"
                            class="flex-1 px-4 py-2 border rounded-lg text-sm font-medium transition-colors"
                            :class="form.type === 'expense' ? 'bg-destructive text-destructive-foreground border-destructive' : 'border-border hover:bg-muted text-foreground'"
                        >
                            รายจ่าย
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'income'"
                            class="flex-1 px-4 py-2 border rounded-lg text-sm font-medium transition-colors"
                            :class="form.type === 'income' ? 'bg-success text-success-foreground border-success' : 'border-border hover:bg-muted text-foreground'"
                        >
                            รายรับ
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'adjustment'"
                            class="flex-1 px-4 py-2 border rounded-lg text-sm font-medium transition-colors"
                            :class="form.type === 'adjustment' ? 'bg-primary text-primary-foreground border-primary' : 'border-border hover:bg-muted text-foreground'"
                        >
                            ปรับ
                        </button>
                    </div>
                </div>

                <!-- Wallet & Amount -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="wallet" class="block text-sm font-medium text-foreground mb-1">กระเป๋าเงิน <span class="text-destructive">*</span></label>
                        <select
                            id="wallet"
                            x-model="form.wallet_id"
                            @change="clearError('wallet_id')"
                            :disabled="isViewMode"
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="errors.wallet_id ? 'border-destructive' : ''"
                            required
                        >
                            <option value="">เลือกกระเป๋าเงิน</option>
                            <template x-for="wallet in wallets" :key="wallet.id">
                                <option :value="wallet.id" x-text="wallet.name"></option>
                            </template>
                        </select>
                        <p x-show="errors.wallet_id" class="mt-1 text-xs text-destructive" x-text="errors.wallet_id"></p>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-foreground mb-1">จำนวนเงิน (THB) <span class="text-destructive">*</span></label>
                        <input
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            x-model="form.amount"
                            @input="clearError('amount')"
                            :disabled="isViewMode"
                            class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="errors.amount ? 'border-destructive' : ''"
                            placeholder="0.00"
                            required
                        >
                        <p x-show="errors.amount" class="mt-1 text-xs text-destructive" x-text="errors.amount"></p>
                    </div>
                </div>

                <!-- Date Time -->
                <div>
                    <label for="transacted_at" class="block text-sm font-medium text-foreground mb-1">วันเวลาทำรายการ <span class="text-destructive">*</span></label>
                    <input
                        id="transacted_at"
                        type="datetime-local"
                        x-model="form.transacted_at"
                        @input="clearError('transacted_at')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="errors.transacted_at ? 'border-destructive' : ''"
                        required
                    >
                    <p x-show="errors.transacted_at" class="mt-1 text-xs text-destructive" x-text="errors.transacted_at"></p>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-foreground mb-1">หมวดหมู่ <span class="text-destructive">*</span></label>
                    <select
                        id="category"
                        x-model="form.category_id"
                        @change="clearError('category_id')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
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
                <div>
                    <label for="sender" class="block text-sm font-medium text-foreground mb-1">ผู้โอน <span class="text-destructive">*</span></label>
                    <input
                        id="sender"
                        type="text"
                        x-model="form.sender"
                        @input="clearError('sender')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="errors.sender ? 'border-destructive' : ''"
                        placeholder="ชื่อผู้โอน"
                        required
                    >
                    <p x-show="errors.sender" class="mt-1 text-xs text-destructive" x-text="errors.sender"></p>
                </div>

                <!-- Recipient -->
                <div>
                    <label for="recipient" class="block text-sm font-medium text-foreground mb-1">ผู้รับ <span class="text-destructive">*</span></label>
                    <input
                        id="recipient"
                        type="text"
                        x-model="form.recipient"
                        @input="clearError('recipient')"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="errors.recipient ? 'border-destructive' : ''"
                        placeholder="ชื่อผู้รับ"
                        required
                    >
                    <p x-show="errors.recipient" class="mt-1 text-xs text-destructive" x-text="errors.recipient"></p>
                </div>

                <!-- Transaction Reference -->
                <div>
                    <label for="transaction_ref" class="block text-sm font-medium text-foreground mb-1">เลขอ้างอิง</label>
                    <input
                        id="transaction_ref"
                        type="text"
                        x-model="form.transaction_ref"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="เลขอ้างอิงธุรกรรม"
                        x-show="isCreateMode"
                        style="display: none;"
                    >
                    <div
                        x-show="!isCreateMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-muted/50 text-foreground"
                        style="display: none;"
                    >
                        <span x-text="form.transaction_ref || '-'"></span>
                    </div>
                </div>

                <!-- Note -->
                <div>
                    <label for="note" class="block text-sm font-medium text-foreground mb-1">บันทึกย่อ</label>
                    <textarea
                        id="note"
                        rows="3"
                        x-model="form.note"
                        :disabled="isViewMode"
                        class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background resize-none disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="บันทึกเพิ่มเติม..."
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex gap-3 p-6 border-t border-border bg-muted/50">
            <!-- Create Mode Footer -->
            <template x-if="isCreateMode">
                <div class="flex gap-3 w-full">
                    <button
                        @click="closeModal()"
                        class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground"
                        :disabled="loading"
                    >
                        ยกเลิก
                    </button>
                    <button
                        @click="submit()"
                        class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="loading"
                    >
                        <template x-if="loading">
                            <span class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            <template x-if="isViewMode">
                <div class="flex gap-3 w-full">
                    <button
                        @click="toggleEditMode()"
                        class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors"
                    >
                        แก้ไข
                    </button>
                    <button
                        @click="delete()"
                        class="flex-1 px-4 py-2 bg-destructive hover:bg-destructive/90 text-destructive-foreground rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="loading"
                    >
                        ลบ
                    </button>
                </div>
            </template>

            <!-- Edit Mode Footer -->
            <template x-if="isEditMode">
                <div class="flex gap-3 w-full">
                    <button
                        @click="toggleEditMode()"
                        class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground"
                    >
                        ยกเลิก
                    </button>
                    <button
                        @click="submit()"
                        class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="loading"
                    >
                        <template x-if="loading">
                            <span class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                        class="px-4 py-2 bg-destructive hover:bg-destructive/90 text-destructive-foreground rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
