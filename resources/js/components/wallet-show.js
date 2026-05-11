export function walletShow() {
    return {
        walletId: null,
        editOpen: false,
        adjustOpen: false,
        draft: { type: 'bank' },
        activeTab: 'transactions',

        initWallet(id, type) {
            this.walletId = id;
            this.draft.type = type;
        },

        async deleteWallet() {
            if (!confirm('คุณต้องการลบกระเป๋าเงินนี้ใช่หรือไม่?')) {
                return;
            }

            try {
                const response = await fetch(`/wallets/${this.walletId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.$store.toast.success('ลบบัญชีเรียบร้อยแล้ว');
                    setTimeout(() => {
                        window.location.href = '/wallets';
                    }, 500);
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to delete wallet:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการลบ');
            }
        }
    };
}
