export function categories() {
    return {
        categories: [],
        fixedCategories: [],
        editing: null,
        open: false,
        isPremium: true,

        async loadCategories() {
            try {
                const [catRes, fixedRes] = await Promise.all([
                    fetch('/categories/data'),
                    fetch('/categories/fixed'),
                ]);
                const catData = await catRes.json();
                const fixedData = await fixedRes.json();
                this.categories = catData.categories;
                this.fixedCategories = fixedData.fixed_categories;
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },

        startNew() {
            if (!this.isPremium) {
                this.$paywall?.open();
                return;
            }
            this.editing = {
                id: null,
                fixed_category_id: this.fixedCategories.length > 0 ? this.fixedCategories[0].id : null,
                name: '',
                icon: '📌',
            };
            this.open = true;
        },

        startEdit(category) {
            if (!this.isPremium) {
                this.$paywall?.open();
                return;
            }
            this.editing = { ...category };
            this.open = true;
        },

        async save() {
            if (!this.isPremium) {
                this.$paywall?.open();
                return;
            }

            if (!this.editing || !this.editing.name.trim()) {
                this.$store.toast.error('กรุณาตั้งชื่อหมวดหมู่');
                return;
            }

            if (!this.editing.fixed_category_id) {
                this.$store.toast.error('กรุณาเลือกหมวดหมู่หลัก');
                return;
            }

            try {
                const url = this.editing.id ? `/categories/${this.editing.id}` : '/categories';
                const method = this.editing.id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        fixed_category_id: this.editing.fixed_category_id,
                        name: this.editing.name,
                        icon: this.editing.icon,
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCategories();
                    this.open = false;
                    this.$store.toast.success(this.editing.id ? 'แก้ไขหมวดหมู่เรียบร้อยแล้ว' : 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
                } else {
                    if (data.requires_subscription) {
                        this.$paywall?.open();
                    }
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to save category:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการบันทึก');
            }
        },

        async remove(id) {
            if (!this.isPremium) {
                this.$paywall?.open();
                return;
            }

            if (!confirm('คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
                return;
            }

            try {
                const response = await fetch(`/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.categories = this.categories.filter(c => c.id !== id);
                    this.$store.toast.success('ลบหมวดหมู่เรียบร้อยแล้ว');
                } else {
                    if (data.requires_subscription) {
                        this.$paywall?.open();
                    }
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to delete category:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการลบ');
            }
        },

        getFixedCategoryName(fixedCategoryId) {
            const fc = this.fixedCategories.find(c => c.id === fixedCategoryId);
            return fc ? fc.name : '';
        },

        getFixedCategoryColor(fixedCategoryId) {
            const fc = this.fixedCategories.find(c => c.id === fixedCategoryId);
            return fc ? fc.color : '#64748b';
        },
    };
}
