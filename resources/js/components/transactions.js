export function transactions(initialData) {
    return {
        transactions: initialData.transactions || [],
        categories: initialData.categories || [],
        wallets: initialData.wallets || [],
        q: '',
        cat: 'all',
        walType: 'all',
        wal: 'all',
        txFilter: 'all',
        loading: false,

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('wallet')) {
                this.wal = params.get('wallet');
            }
            if (params.has('category')) {
                this.cat = params.get('category');
            }
            if (params.has('q')) {
                this.q = params.get('q');
            }

            this.$watch('q', () => this.refresh());
            this.$watch('cat', () => this.refresh());
            this.$watch('wal', () => this.refreshWithWalletType());
            this.$watch('walType', () => {
                this.wal = 'all';
                this.refreshWithWalletType();
            });
            this.$watch('txFilter', () => this.refresh());

            this.refresh();
        },

        get walletTypeLabel() {
            return this.walType === 'all' ? 'ทั้งหมด' : this.walType === 'personal' ? 'ส่วนตัว' : 'แชร์';
        },

        setWalletType(type) {
            this.walType = type;
        },

        get filteredWallets() {
            if (this.walType === 'all') {
                return this.wallets;
            }
            return this.wallets.filter(w => w.access_type === this.walType);
        },

        get list() {
            return this.transactions.filter((t) => {
                if (this.cat !== 'all' && t.category_id !== this.cat) return false;
                if (this.wal !== 'all' && t.wallet_id !== this.wal) return false;
                if (this.q && !t.description.toLowerCase().includes(this.q.toLowerCase())) return false;
                return true;
            });
        },

        get categoryFor() {
            return (id) => this.categories.find(c => c.id === id);
        },

        get walletFor() {
            return (id) => this.wallets.find(w => w.id === id);
        },

        formatTHB(amount) {
            return new Intl.NumberFormat('th-TH', {
                style: 'currency',
                currency: 'THB',
                minimumFractionDigits: 2
            }).format(amount).replace('THB', '').trim();
        },

        formatDateTime(datetime) {
            const d = new Date(datetime);
            return d.toLocaleDateString('th-TH', {
                day: '2-digit',
                month: 'short',
                year: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        exportCsv() {
            const rows = [['วันเวลา', 'รายการ', 'หมวดหมู่', 'กระเป๋า', 'ประเภท', 'จำนวน']];
            this.list.forEach((t) => {
                rows.push([
                    this.formatDateTime(t.transacted_at),
                    t.description,
                    this.categoryFor(t.category_id)?.name ?? '',
                    this.walletFor(t.wallet_id)?.name ?? '',
                    t.type === 'expense' ? 'รายจ่าย' : (t.type === 'income' ? 'รายรับ' : 'ปรับ'),
                    String(t.amount),
                ]);
            });
            const csv = rows.map((r) => r.map((c) => `"${c.split('"').join('""')}"`).join(',')).join('\n');
            const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `transactions-${Date.now()}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        },

        async refreshWithWalletType() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.q) params.append('q', this.q);
                if (this.cat !== 'all') params.append('category', this.cat);
                if (this.walType !== 'all') params.append('wallet_type', this.walType);
                if (this.wal !== 'all') params.append('wallet', this.wal);
                if (this.walType === 'shared' && this.txFilter !== 'all') params.append('transaction_filter', this.txFilter);

                const response = await fetch(`/transactions/data?${params.toString()}`);
                const data = await response.json();
                this.transactions = data.transactions;
            } catch (error) {
                console.error('Failed to load transactions:', error);
            } finally {
                this.loading = false;
            }
        },

        async refresh() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.q) params.append('q', this.q);
                if (this.cat !== 'all') params.append('category', this.cat);
                if (this.walType !== 'all') params.append('wallet_type', this.walType);
                if (this.wal !== 'all') params.append('wallet', this.wal);
                if (this.walType === 'shared' && this.txFilter !== 'all') params.append('transaction_filter', this.txFilter);

                const response = await fetch(`/transactions/data?${params.toString()}`);
                const data = await response.json();
                this.transactions = data.transactions;
            } catch (error) {
                console.error('Failed to load transactions:', error);
            } finally {
                this.loading = false;
            }
        },

        async viewTransaction(transaction) {
            try {
                const response = await fetch(`/transactions/${transaction.id}`);
                const data = await response.json();
                if (data.transaction) {
                    window.dispatchEvent(new CustomEvent('open-transaction-modal-view', { detail: data.transaction }));
                }
            } catch (error) {
                console.error('Failed to load transaction:', error);
            }
        },
    };
}