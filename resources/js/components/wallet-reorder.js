import Sortable from 'sortablejs';

export function walletReorder() {
    return {
        sortable: null,
        saving: false,

        init() {
            this.$nextTick(() => {
                const el = this.$refs.grid;
                if (!el) return;

                this.sortable = new Sortable(el, {
                    animation: 200,
                    handle: '[data-drag-handle]',
                    ghostClass: 'opacity-30',
                    dragClass: 'shadow-lg',
                    onEnd: () => this.saveOrder(),
                });
            });
        },

        async saveOrder() {
            const el = this.$refs.grid;
            if (!el) return;

            const ids = [...el.children].map(child => child.dataset.walletId).filter(Boolean);

            if (ids.length === 0) return;

            this.saving = true;

            try {
                const response = await fetch('/wallets/reorder', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids }),
                });

                const data = await response.json();

                if (data.success) {
                    this.$store.toast.success(data.message);
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch {
                this.$store.toast.error('เกิดข้อผิดพลาดในการจัดเรียง');
            } finally {
                this.saving = false;
            }
        },
    };
}
