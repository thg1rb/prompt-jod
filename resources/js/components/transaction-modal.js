export function transactionModal(initialData) {
    return {
        open: initialData?.initialOpen || false,
        mode: 'create', // 'create' | 'view' | 'edit'
        editingId: null,
        loading: false,
        verifying: false,
        slipData: null,
        slipError: null,
        slipImagePreview: null,

        // Suggestions
        senderSuggestions: [],
        recipientSuggestions: [],
        showSenderSuggestions: false,
        showRecipientSuggestions: false,
        senderSearchTimeout: null,
        recipientSearchTimeout: null,
        senderHighlightedIndex: -1,
        recipientHighlightedIndex: -1,

        // Form data
        form: {
            wallet_id: '',
            category_id: '',
            type: 'expense',
            amount: '',
            sender: '',
            recipient: '',
            note: '',
            transacted_at: (() => {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            })(),
            transaction_ref: '',
        },

        // Available options
        wallets: initialData?.wallets || [],
        categories: initialData?.categories || [],

        // Errors
        errors: {},

        get title() {
            if (this.mode === 'create') return 'เพิ่มธุรกรรมใหม่';
            if (this.mode === 'view') return 'รายละเอียดธุรกรรม';
            return 'แก้ไขธุรกรรม';
        },

        get submitText() {
            return this.loading ? 'กำลังบันทึก...' : (this.mode === 'edit' ? 'บันทึกการแก้ไข' : 'บันทึก');
        },

        get isViewMode() {
            return this.mode === 'view';
        },

        get isEditMode() {
            return this.mode === 'edit';
        },

        get isCreateMode() {
            return this.mode === 'create';
        },

        get selectedWallet() {
            return this.wallets.find(w => w.id === this.form.wallet_id);
        },

        get selectedWalletType() {
            return this.selectedWallet?.type || '';
        },

        get isCashWallet() {
            return this.selectedWalletType === 'cash';
        },

        get senderLabel() {
            return this.isCashWallet ? 'ผู้จ่าย' : 'ผู้โอน';
        },

        openModal() {
            this.open = true;
            this.resetForm();
        },

        closeModal() {
            this.open = false;
            setTimeout(() => {
                this.resetForm();
            }, 150);
        },

        resetForm() {
            this.mode = 'create';
            this.editingId = null;
            this.form = {
                wallet_id: this.wallets.find(w => w.is_default)?.id || '',
                category_id: '',
                type: 'expense',
                amount: '',
                sender: '',
                recipient: '',
                note: '',
                transacted_at: (() => {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                })(),
                transaction_ref: '',
            };
            this.slipData = null;
            this.slipImagePreview = null;
            this.slipError = null;
            this.errors = {};
        },

        openView(transactionData) {
            this.mode = 'view';
            this.editingId = transactionData.id;
            this.form = {
                wallet_id: transactionData.wallet_id || '',
                category_id: transactionData.category_id || '',
                type: transactionData.type || 'expense',
                amount: transactionData.amount || '',
                sender: transactionData.sender || '',
                recipient: transactionData.recipient || '',
                note: transactionData.note || '',
                transacted_at: transactionData.transacted_at || '',
                transaction_ref: transactionData.transaction_ref || '',
            };
            this.slipData = null;
            this.slipImagePreview = null;
            this.slipError = null;
            this.errors = {};
            this.open = true;
        },

        openEdit(transactionData) {
            this.mode = 'edit';
            this.editingId = transactionData.id;
            this.form = {
                wallet_id: transactionData.wallet_id || '',
                category_id: transactionData.category_id || '',
                type: transactionData.type || 'expense',
                amount: transactionData.amount || '',
                sender: transactionData.sender || '',
                recipient: transactionData.recipient || '',
                note: transactionData.note || '',
                transacted_at: transactionData.transacted_at || '',
                transaction_ref: transactionData.transaction_ref || '',
            };
            this.slipData = null;
            this.slipImagePreview = null;
            this.slipError = null;
            this.errors = {};
            this.open = true;
        },

        toggleEditMode() {
            if (this.mode === 'view') {
                this.mode = 'edit';
            } else {
                this.mode = 'view';
            }
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.slipImagePreview = URL.createObjectURL(file);
            this.slipError = null;
            this.verifying = true;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/transactions/verify-slip', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.slipData = data.slip;
                    this.autoFillFromSlip(data.slip);
                } else {
                    this.slipError = data.error || 'ไม่สามารถอ่านข้อมูลจากสลิปได้';
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.slipError = 'เกิดข้อผิดพลาดในการอัพโหลด';
            } finally {
                this.verifying = false;
            }
        },

        async handleDrop(event) {
            event.preventDefault();
            event.stopPropagation();

            const file = event.dataTransfer.files[0];
            if (!file) return;

            const input = this.$refs.fileInput;
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;

            await this.handleFileUpload({ target: { files: [file] } });
        },

        autoFillFromSlip(slip) {
            if (slip.amount) {
                this.form.amount = slip.amount;
            }
            if (slip.date) {
                try {
                    const isoStr = slip.date;
                    const [datePart, timePart] = isoStr.split('T');
                    const [year, month, day] = datePart.split('-');
                    const timeWithTz = timePart.split('+')[0].split('-')[0];
                    const [hours, minutes] = timeWithTz.split(':');
                    if (year && month && day && hours && minutes) {
                        const newDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                        this.$nextTick(() => {
                            this.form.transacted_at = newDateTime;
                        });
                    }
                } catch (e) {
                    console.error('Date parsing error:', e);
                }
            }
            if (slip.sender_name) {
                this.form.sender = slip.sender_name;
            }
            if (slip.receiver_name) {
                this.form.recipient = slip.receiver_name;
            }
            if (slip.transaction_ref) {
                this.form.transaction_ref = slip.transaction_ref;
            }
        },

        async submit() {
            this.loading = true;
            this.errors = {};

            const url = this.mode === 'edit' ? `/transactions/${this.editingId}` : '/transactions';
            const method = this.mode === 'edit' ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    const text = await response.text();
                    console.error('Response not JSON:', text);
                    this.errors = { _form: 'เกิดข้อผิดพลาดในการบันทึก' };
                    return;
                }

                if (response.ok && data.success) {
                    const event = this.mode === 'edit' ? 'transaction-updated' : 'transaction-created';
                    this.$store.toast.success(this.mode === 'edit' ? 'แก้ไขธุรกรรมเรียบร้อยแล้ว' : 'บันทึกธุรกรรมเรียบร้อยแล้ว');
                    window.dispatchEvent(new CustomEvent(event));
                    if (this.mode === 'edit') {
                        this.mode = 'view';
                        this.loading = false;
                    } else {
                        this.closeModal();
                    }
                } else {
                    if (response.status === 422 && data.errors) {
                        const flattenedErrors = {};
                        for (const field in data.errors) {
                            flattenedErrors[field] = Array.isArray(data.errors[field])
                                ? data.errors[field][0]
                                : data.errors[field];
                        }
                        this.errors = flattenedErrors;
                    } else {
                        this.errors = { _form: data.message || 'เกิดข้อผิดพลาดในการบันทึก' };
                    }
                }
            } catch (error) {
                console.error('Submit error:', error);
                this.errors = { _form: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตแล้วลองใหม่' };
                this.$store.toast.error('ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตแล้วลองใหม่');
            } finally {
                if (this.mode !== 'edit') {
                    this.loading = false;
                }
            }
        },

        async delete() {
            if (!confirm('ยืนยันที่จะลบธุรกรรมนี้?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/transactions/${this.editingId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.$store.toast.success('ลบธุรกรรมเรียบร้อยแล้ว');
                    window.dispatchEvent(new CustomEvent('transaction-deleted'));
                    this.closeModal();
                } else {
                    this.$store.toast.error(data.message || 'ไม่สามารถลบธุรกรรมได้');
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.$store.toast.error('ไม่สามารถลบธุรกรรมได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต');
            } finally {
                this.loading = false;
            }
        },

        formatTHB(amount) {
            if (!amount) return '';
            return new Intl.NumberFormat('th-TH', {
                style: 'currency',
                currency: 'THB',
                minimumFractionDigits: 2
            }).format(amount).replace('THB', '').trim();
        },

        onSenderInput() {
            this.clearError('sender');
            this.senderHighlightedIndex = -1;

            if (!this.form.sender || this.form.sender.length < 1) {
                this.showSenderSuggestions = false;
                this.senderSuggestions = [];
                return;
            }

            clearTimeout(this.senderSearchTimeout);
            this.senderSearchTimeout = setTimeout(() => {
                this.searchNames(this.form.sender, 'sender');
            }, 300);
        },

        onSenderFocus() {
            this.senderHighlightedIndex = -1;
            this.searchNames('', 'sender');
        },

        onRecipientInput() {
            this.clearError('recipient');
            this.recipientHighlightedIndex = -1;

            if (!this.form.recipient || this.form.recipient.length < 1) {
                this.showRecipientSuggestions = false;
                this.recipientSuggestions = [];
                return;
            }

            clearTimeout(this.recipientSearchTimeout);
            this.recipientSearchTimeout = setTimeout(() => {
                this.searchNames(this.form.recipient, 'recipient');
            }, 300);
        },

        onRecipientFocus() {
            this.recipientHighlightedIndex = -1;
            this.searchNames('', 'recipient');
        },

        async searchNames(query, field) {
            try {
                const url = new URL('/transactions/search-names', window.location.origin);
                url.searchParams.append('q', query);
                url.searchParams.append('field', field);

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                });

                const data = await response.json();

                if (field === 'sender') {
                    this.senderSuggestions = data.names || [];
                    this.senderHighlightedIndex = -1;
                    this.showSenderSuggestions = this.senderSuggestions.length > 0;
                } else {
                    this.recipientSuggestions = data.names || [];
                    this.recipientHighlightedIndex = -1;
                    this.showRecipientSuggestions = this.recipientSuggestions.length > 0;
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        selectSenderSuggestion(name) {
            this.form.sender = name;
            this.showSenderSuggestions = false;
            this.senderSuggestions = [];
            this.senderHighlightedIndex = -1;
        },

        selectRecipientSuggestion(name) {
            this.form.recipient = name;
            this.showRecipientSuggestions = false;
            this.recipientSuggestions = [];
            this.recipientHighlightedIndex = -1;
        },

        hideSuggestions() {
            setTimeout(() => {
                this.showSenderSuggestions = false;
                this.showRecipientSuggestions = false;
                this.senderHighlightedIndex = -1;
                this.recipientHighlightedIndex = -1;
            }, 200);
        },

        onSenderKeydown(event) {
            if (!this.showSenderSuggestions || this.senderSuggestions.length === 0) return;

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.senderHighlightedIndex = Math.min(
                        this.senderHighlightedIndex + 1,
                        this.senderSuggestions.length - 1
                    );
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.senderHighlightedIndex = Math.max(this.senderHighlightedIndex - 1, 0);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.senderHighlightedIndex >= 0 && this.senderHighlightedIndex < this.senderSuggestions.length) {
                        this.selectSenderSuggestion(this.senderSuggestions[this.senderHighlightedIndex]);
                    }
                    break;
                case 'Escape':
                    event.preventDefault();
                    this.showSenderSuggestions = false;
                    this.senderHighlightedIndex = -1;
                    break;
            }
        },

        onRecipientKeydown(event) {
            if (!this.showRecipientSuggestions || this.recipientSuggestions.length === 0) return;

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.recipientHighlightedIndex = Math.min(
                        this.recipientHighlightedIndex + 1,
                        this.recipientSuggestions.length - 1
                    );
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.recipientHighlightedIndex = Math.max(this.recipientHighlightedIndex - 1, 0);
                    break;
                case 'Enter':
                    event.preventDefault();
                    if (this.recipientHighlightedIndex >= 0 && this.recipientHighlightedIndex < this.recipientSuggestions.length) {
                        this.selectRecipientSuggestion(this.recipientSuggestions[this.recipientHighlightedIndex]);
                    }
                    break;
                case 'Escape':
                    event.preventDefault();
                    this.showRecipientSuggestions = false;
                    this.recipientHighlightedIndex = -1;
                    break;
            }
        },
    };
}
