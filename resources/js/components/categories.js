export function categories() {
    return {
        categories: [],
        editing: null,
        open: false,
        keywordInput: '',
        PALETTE: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#0EA5E9', '#64748B', '#14B8A6'],

        async loadCategories() {
            try {
                const response = await fetch('/categories/data');
                const data = await response.json();
                this.categories = data.categories.map(c => ({
                    ...c,
                    keywords: c.rules ? c.rules.map(r => r.keyword) : []
                }));
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },

        startNew() {
            this.editing = {
                id: null,
                name: '',
                icon: '📌',
                color: this.PALETTE[0],
                keywords: []
            };
            this.keywordInput = '';
            this.open = true;
        },

        startEdit(category) {
            this.editing = { ...category };
            this.keywordInput = category.keywords.join(', ');
            this.open = true;
        },

        updateKeywords() {
            if (this.editing) {
                this.editing.keywords = this.keywordInput
                    .split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
            }
        },

        async save() {
            if (!this.editing || !this.editing.name.trim()) {
                alert('กรุณาตั้งชื่อหมวดหมู่');
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
                        name: this.editing.name,
                        icon: this.editing.icon,
                        color: this.editing.color,
                        keywords: this.editing.keywords
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCategories();
                    this.open = false;
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to save category:', error);
                alert('เกิดข้อผิดพลาดในการบันทึก');
            }
        },

        async remove(id) {
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
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to delete category:', error);
                alert('เกิดข้อผิดพลาดในการลบ');
            }
        }
    };
}
