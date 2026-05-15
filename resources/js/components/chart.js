async function getApexCharts() {
    const { default: ApexCharts } = await import('apexcharts');
    return ApexCharts;
}

export function pieChart(initialData = {}) {
    return {
        chart: null,
        categories: initialData.categories || [],

        init() {
            this.renderChart();
            this.$watch('categories', () => this.updateChart());
            this.$watch('darkMode', () => this.updateChart());
        },

        async renderChart() {
            const ApexCharts = await getApexCharts();
            const colors = this.categories.map(c => c.color || '#6366f1');
            const labels = this.categories.map(c => c.name);
            const values = this.categories.map(c => c.value);

            const options = {
                series: values,
                labels: labels,
                chart: {
                    type: 'donut',
                    height: 260,
                    fontFamily: 'Sarabun, sans-serif',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                        },
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                stroke: {
                    show: true,
                    colors: this.getColors().card,
                    width: 2,
                },
                colors: colors,
                tooltip: {
                    theme: this.getColors().theme,
                    y: {
                        formatter: (value) => {
                            return parseFloat(value).toLocaleString('th-TH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            });
                        },
                    },
                },
            };

            this.chart = new ApexCharts(this.$el, options);
            this.chart.render();
        },

        updateChart() {
            if (!this.chart) return;

            const colors = this.categories.map(c => c.color || '#6366f1');
            const labels = this.categories.map(c => c.name);
            const values = this.categories.map(c => c.value);

            this.chart.updateOptions({
                labels: labels,
                colors: colors,
                stroke: {
                    colors: this.getColors().card,
                },
                tooltip: {
                    theme: this.getColors().theme,
                },
            });

            this.chart.updateSeries(values);
        },

        getColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                card: isDark ? '#1e293b' : '#ffffff',
                theme: isDark ? 'dark' : 'light',
            };
        },

        get darkMode() {
            return document.documentElement.classList.contains('dark');
        },
    };
}

export function barChart(initialData = {}) {
    return {
        chart: null,
        data: initialData.data || [],

        init() {
            this.renderChart();
            this.$watch('data', () => this.updateChart());
            this.$watch('darkMode', () => this.updateChart());
        },

        async renderChart() {
            const ApexCharts = await getApexCharts();
            const days = this.data.map(d => d.day);
            const amounts = this.data.map(d => d.amount);

            const options = {
                series: [{
                    name: 'ยอดใช้จ่าย',
                    data: amounts,
                }],
                chart: {
                    type: 'bar',
                    height: 260,
                    fontFamily: 'Sarabun, sans-serif',
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '60%',
                    },
                },
                xaxis: {
                    categories: days,
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        style: {
                            colors: this.getColors().text,
                            fontSize: '12px',
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: this.getColors().text,
                            fontSize: '12px',
                        },
                        formatter: (value) => {
                            return value.toLocaleString('th-TH');
                        },
                    },
                },
                grid: {
                    borderColor: this.getColors().border,
                    strokeDashArray: 4,
                },
                tooltip: {
                    theme: this.getColors().theme,
                    y: {
                        formatter: (value) => {
                            return parseFloat(value).toLocaleString('th-TH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            });
                        },
                    },
                },
                colors: [this.getColors().primary],
                dataLabels: {
                    enabled: false,
                },
            };

            this.chart = new ApexCharts(this.$el, options);
            this.chart.render();
        },

        updateChart() {
            if (!this.chart) return;

            const amounts = this.data.map(d => d.amount);

            this.chart.updateOptions({
                xaxis: {
                    labels: {
                        style: {
                            colors: this.getColors().text,
                        },
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: this.getColors().text,
                        },
                    },
                },
                grid: {
                    borderColor: this.getColors().border,
                },
                tooltip: {
                    theme: this.getColors().theme,
                },
            });

            this.chart.updateSeries([{
                data: amounts,
            }]);
        },

        getColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                text: isDark ? '#94a3b8' : '#64748b',
                border: isDark ? '#334155' : '#e2e8f0',
                primary: isDark ? '#60a5fa' : '#3b82f6',
                theme: isDark ? 'dark' : 'light',
            };
        },

        get darkMode() {
            return document.documentElement.classList.contains('dark');
        },
    };
}

/**
 * Dashboard Component
 * Manages time range filtering and updates all dashboard components
 * Usage: x-data="dashboard()" x-init="init()"
 */
export function dashboard(initialData = {}) {
    return {
        range: 'month',
        walletType: 'all',
        loading: false,

        totalExpenses: initialData.totalExpenses || '0.00',
        totalBalance: initialData.totalBalance || '0.00',
        averagePerTransaction: initialData.averagePerTransaction || '0.00',
        topCategory: initialData.topCategory || null,
        filteredCount: initialData.filteredCount || 0,
        walletCount: initialData.walletCount || 0,

        categoryData: initialData.categoryData || [],
        sevenDaySpending: initialData.sevenDaySpending || [],

        recentTransactions: initialData.recentTransactions || [],

        init() {
            console.log('Dashboard init:', { totalExpenses: this.totalExpenses, categoryData: this.categoryData });
        },

        async loadDashboardData() {
            if (this.loading) return;
            this.loading = true;

            try {
                const params = new URLSearchParams({ range: this.range, wallet_type: this.walletType });
                const response = await fetch(`/dashboard/filter?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error('Failed to fetch dashboard data');

                const data = await response.json();

                this.totalExpenses = data.totalExpenses;
                this.totalBalance = data.totalBalance;
                this.averagePerTransaction = data.averagePerTransaction;
                this.topCategory = data.topCategory;
                this.filteredCount = data.filteredCount;
                this.walletCount = data.walletCount;
                this.categoryData = data.categoryData;
                this.sevenDaySpending = data.sevenDaySpending;
                this.recentTransactions = data.recentTransactions;
            } catch (err) {
                console.error('Failed to fetch dashboard data:', err);
            } finally {
                this.loading = false;
            }
        },

        async setRange(newRange) {
            if (this.range === newRange) return;
            this.range = newRange;
            await this.loadDashboardData();
        },

        async setWalletType(newType) {
            if (this.walletType === newType) return;
            this.walletType = newType;
            await this.loadDashboardData();
        },

        formatAmount(amount) {
            return parseFloat(amount).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        formatRelativeTime(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'เมื่อสักครู่';
            if (diffMins < 60) return `${diffMins} นาทีที่แล้ว`;
            if (diffHours < 24) return `${diffHours} ชั่วโมงที่แล้ว`;
            if (diffDays < 7) return `${diffDays} วันที่แล้ว`;

            return date.toLocaleDateString('th-TH', {
                day: 'numeric',
                month: 'short',
                year: '2-digit',
            });
        },
    };
}
