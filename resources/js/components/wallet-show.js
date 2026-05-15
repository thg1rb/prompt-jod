export function walletShow() {
    return {
        walletId: null,
        walletType: 'personal',
        editOpen: false,
        adjustOpen: false,
        draft: { type: 'bank' },
        activeTab: 'transactions',
        categories: [],
        fixedCategories: [],
        editing: null,
        open: false,

        initWallet(id, type, accessType = 'personal') {
            this.walletId = id;
            this.walletType = accessType;
            this.draft.type = type;
            if (accessType === 'shared') {
                this.loadCategories();
            }

            window.addEventListener('open-edit-modal', () => {
                this.editOpen = true;
            });
        },

        async loadCategories() {
            try {
                const [catRes, fixedRes] = await Promise.all([
                    fetch(`/wallets/${this.walletId}/categories`),
                    fetch('/categories/fixed'),
                ]);
                const catData = await catRes.json();
                const fixedData = await fixedRes.json();
                this.categories = catData.categories;
                this.fixedCategories = fixedData.fixed_categories;
            } catch (error) {
                console.error('Failed to load wallet categories:', error);
            }
        },

        startNewCategory() {
            this.editing = {
                id: null,
                fixed_category_id: this.fixedCategories.length > 0 ? this.fixedCategories[0].id : null,
                name: '',
                icon: '📌',
            };
            this.open = true;
        },

        startEditCategory(cat) {
            this.editing = { ...cat };
            this.open = true;
        },

        async saveCategory() {
            if (!this.editing || !this.editing.name.trim() || !this.editing.fixed_category_id) return;

            try {
                const url = this.editing.id
                    ? `/wallets/${this.walletId}/categories/${this.editing.id}`
                    : `/wallets/${this.walletId}/categories`;
                const method = this.editing.id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        fixed_category_id: this.editing.fixed_category_id,
                        name: this.editing.name,
                        icon: this.editing.icon,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    await this.loadCategories();
                    this.open = false;
                    this.$store.toast.success(this.editing.id ? 'แก้ไขหมวดหมู่เรียบร้อยแล้ว' : 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to save category:', error);
            }
        },

        async removeCategory(id) {
            if (!confirm('คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) return;

            try {
                const response = await fetch(`/wallets/${this.walletId}/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.categories = this.categories.filter(c => c.id !== id);
                    this.$store.toast.success('ลบหมวดหมู่เรียบร้อยแล้ว');
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to delete category:', error);
            }
        },

        startEdit() {
            this.editOpen = true;
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
        },
    };
}