import ApexCharts from 'apexcharts';

/**
 * Pie Chart Component
 * Usage: x-data="pieChart({ categories: [...] })"
 */
export function pieChart(initialData = {}) {
    return {
        chart: null,
        categories: initialData.categories || [],

        init() {
            this.renderChart();
            this.$watch('categories', () => this.updateChart());
            this.$watch('darkMode', () => this.updateChart());
        },

        renderChart() {
            const colors = this.categories.map(c => c.color || '#6366f1');
            const labels = this.categories.map(c => c.name);
            const values = this.categories.map(c => c.value);

            const options = {
                series: values,
                labels: labels,
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Sarabun, sans-serif',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    color: this.getColors().text,
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 600,
                                    color: this.getColors().text,
                                },
                                total: {
                                    show: true,
                                    label: 'รวม',
                                    color: this.getColors().text,
                                },
                            },
                        },
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '12px',
                    itemMargin: { horizontal: 8, vertical: 4 },
                    labels: {
                        colors: this.getColors().text,
                    },
                },
                stroke: {
                    show: true,
                    colors: this.getColors().border,
                },
                colors: colors,
                tooltip: {
                    theme: this.getColors().theme,
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
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: {
                                    color: this.getColors().text,
                                },
                                value: {
                                    color: this.getColors().text,
                                },
                                total: {
                                    color: this.getColors().text,
                                },
                            },
                        },
                    },
                },
                legend: {
                    labels: {
                        colors: this.getColors().text,
                    },
                },
                stroke: {
                    colors: this.getColors().border,
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
                text: isDark ? '#f8fafc' : '#0f172a',
                border: isDark ? '#1e293b' : '#ffffff',
                theme: isDark ? 'dark' : 'light',
            };
        },

        get darkMode() {
            return document.documentElement.classList.contains('dark');
        },
    };
}

/**
 * Bar Chart Component
 * Usage: x-data="barChart({ data: [...] })"
 */
export function barChart(initialData = {}) {
    return {
        chart: null,
        data: initialData.data || [],

        init() {
            this.renderChart();
            this.$watch('data', () => this.updateChart());
            this.$watch('darkMode', () => this.updateChart());
        },

        renderChart() {
            const days = this.data.map(d => d.day);
            const amounts = this.data.map(d => d.amount);

            const options = {
                series: [{
                    name: 'ยอดใช้จ่าย',
                    data: amounts,
                }],
                chart: {
                    type: 'bar',
                    height: 300,
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
                    borderColor: this.getColors().grid,
                    strokeDashArray: 4,
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 10,
                    },
                },
                tooltip: {
                    theme: this.getColors().theme,
                    y: {
                        formatter: (value) => {
                            return value.toLocaleString('th-TH') + ' บาท';
                        },
                    },
                },
                colors: ['#6366f1'],
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
                    borderColor: this.getColors().grid,
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
                text: isDark ? '#f8fafc' : '#0f172a',
                grid: isDark ? '#334155' : '#e2e8f0',
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
 * Usage: x-data="dashboard()"
 */
export function dashboard() {
    return {
        range: 'month',
        loading: false,
        error: null,

        // Summary data
        totalExpenses: '0.00',
        totalBalance: '0.00',
        averagePerTransaction: '0.00',
        topCategory: null,

        // Chart data
        categoryData: [],
        sevenDaySpending: [],

        // Recent transactions
        recentTransactions: [],

        init() {
            // Load initial data from the view
            this.loadInitialData();
        },

        loadInitialData() {
            // Data is already loaded by the controller
            // This method is for any additional initialization
        },

        async changeRange(newRange) {
            if (this.loading) return;

            this.range = newRange;
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(`/dashboard/filter?range=${newRange}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch dashboard data');
                }

                const data = await response.json();

                // Update summary cards
                this.totalExpenses = data.totalExpenses;
                this.totalBalance = data.totalBalance;
                this.averagePerTransaction = data.averagePerTransaction;
                this.topCategory = data.topCategory;

                // Update chart data
                this.categoryData = data.categoryData;
                this.sevenDaySpending = data.sevenDaySpending;

                // Update transactions
                this.recentTransactions = data.recentTransactions;
            } catch (err) {
                this.error = err.message;
                console.error('Failed to fetch dashboard data:', err);
            } finally {
                this.loading = false;
            }
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

        getTransactionIcon(type) {
            switch (type) {
                case 'expense':
                    return `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>`;
                case 'income':
                    return `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>`;
                default:
                    return `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>`;
            }
        },
    };
}
