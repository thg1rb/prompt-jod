export function walletCategories(walletId) {
    return {
        walletId: walletId,
        categories: [],
        fixedCategories: [],
        editing: null,
        open: false,

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

        startNew() {
            this.editing = {
                id: null,
                fixed_category_id: this.fixedCategories.length > 0 ? this.fixedCategories[0].id : null,
                name: '',
                icon: '📌',
            };
            this.open = true;
        },

        async save() {
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
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to save category:', error);
            }
        },

        async remove(id) {
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
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to delete category:', error);
            }
        },
    };
}
