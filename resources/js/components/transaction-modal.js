export function transactionModal(initialData) {
    return {
        open: false,
        loading: false,
        verifying: false,
        slipData: null,
        slipError: null,
        slipImagePreview: null,

        // Form data
        form: {
            wallet_id: '',
            category_id: '',
            type: 'expense',
            amount: '',
            sender: '',
            sender_bank: '',
            recipient: '',
            note: '',
            transacted_at: new Date().toISOString().split('T')[0],
            transaction_ref: '',
        },

        // Available options
        wallets: initialData.wallets || [],
        categories: initialData.categories || [],

        // Errors
        errors: {},

        openModal() {
            this.open = true;
            this.resetForm();
        },

        closeModal() {
            this.open = false;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                wallet_id: this.wallets.find(w => w.is_default)?.id || '',
                category_id: '',
                type: 'expense',
                amount: '',
                sender: '',
                sender_bank: '',
                recipient: '',
                note: '',
                transacted_at: new Date().toISOString().split('T')[0],
                transaction_ref: '',
            };
            this.slipData = null;
            this.slipImagePreview = null;
            this.slipError = null;
            this.errors = {};
        },

        triggerFileUpload() {
            if (this.verifying || this.slipImagePreview) return;
            document.getElementById('slip-upload').click();
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Show preview
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

            const input = document.getElementById('slip-upload');
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
                this.form.transacted_at = slip.date;
            }
            if (slip.sender_name) {
                this.form.sender = slip.sender_name;
            }
            if (slip.sender_bank) {
                this.form.sender_bank = slip.sender_bank;
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

            try {
                const response = await fetch('/transactions', {
                    method: 'POST',
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
                    // If JSON parsing fails, try to get text response
                    const text = await response.text();
                    console.error('Response not JSON:', text);
                    this.errors = { _form: 'เกิดข้อผิดพลาดในการบันทึก' };
                    return;
                }

                if (response.ok && data.success) {
                    this.closeModal();
                    // Dispatch event to refresh transaction list
                    window.dispatchEvent(new CustomEvent('transaction-created'));
                } else {
                    // Handle validation errors
                    if (response.status === 422 && data.errors) {
                        console.log('Validation errors:', data.errors);
                        // Laravel validation errors format: { field: [error, ...] }
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
    };
}
