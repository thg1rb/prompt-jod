import './bootstrap';

import Alpine from 'alpinejs';
import { pieChart, barChart, dashboard } from './components/chart';
import { categories } from './components/categories';
import { transactions } from './components/transactions';
import { transactionModal } from './components/transaction-modal';
import { walletShow } from './components/wallet-show';
import { walletReorder } from './components/wallet-reorder';

window.Alpine = Alpine;

// Tooltip directive
Alpine.directive('tooltip', (el, { expression }, { evaluateLater, effect }) => {
    let getContent = evaluateLater(expression);
    let tooltip = null;
    let timeout = null;

    const createTooltip = (content) => {
        // Remove existing tooltip
        if (tooltip) {
            document.body.removeChild(tooltip);
        }

        // Create tooltip element
        tooltip = document.createElement('div');
        tooltip.className = 'fixed z-50 px-2.5 py-1 text-xs font-medium text-white bg-gray-900 rounded-md shadow-lg pointer-events-none opacity-0 transition-opacity duration-200';
        tooltip.textContent = content;
        document.body.appendChild(tooltip);

        // Position tooltip
        const rect = el.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        const placement = content?.placement || 'bottom';

        if (placement === 'top') {
            tooltip.style.left = `${rect.left + rect.width / 2 - tooltipRect.width / 2}px`;
            tooltip.style.top = `${rect.top - tooltipRect.height - 8}px`;
        } else {
            // bottom (default)
            tooltip.style.left = `${rect.left + rect.width / 2 - tooltipRect.width / 2}px`;
            tooltip.style.top = `${rect.bottom + 8}px`;
        }

        // Show tooltip with delay
        timeout = setTimeout(() => {
            tooltip.classList.remove('opacity-0');
        }, 200);
    };

    const hideTooltip = () => {
        if (timeout) {
            clearTimeout(timeout);
            timeout = null;
        }
        if (tooltip) {
            tooltip.classList.add('opacity-0');
            setTimeout(() => {
                if (tooltip) {
                    document.body.removeChild(tooltip);
                    tooltip = null;
                }
            }, 150);
        }
    };

    el.addEventListener('mouseenter', () => {
        getContent((content) => {
            createTooltip(content);
        });
    });

    el.addEventListener('mouseleave', hideTooltip);

    el.addEventListener('focus', () => {
        getContent((content) => {
            createTooltip(content);
        });
    });

    el.addEventListener('blur', hideTooltip);

    // Clean up on destroy
    el._x_tooltipCleanup = () => {
        hideTooltip();
        el.removeEventListener('mouseenter', () => {});
        el.removeEventListener('mouseleave', hideTooltip);
        el.removeEventListener('focus', () => {});
        el.removeEventListener('blur', hideTooltip);
    };
});

// Theme store for managing light/dark mode
Alpine.store('theme', {
    isDark: false,
    isTransitioning: false,
    init() {
        // Check localStorage or system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            this.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            this.isDark = false;
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!('theme' in localStorage)) {
                this.isDark = e.matches;
                document.documentElement.classList.toggle('dark', e.matches);
            }
        });
    },
    toggle() {
        if (this.isTransitioning) return;
        this.isTransitioning = true;

        // Add a subtle fade effect
        document.body.style.opacity = '0.95';

        setTimeout(() => {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            }

            // Restore opacity
            setTimeout(() => {
                document.body.style.opacity = '1';
                this.isTransitioning = false;
            }, 200);
        }, 100);
    },
    setDark() {
        this.isDark = true;
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    },
    setLight() {
        this.isDark = false;
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
});

// Mobile menu store
Alpine.store('mobileMenu', {
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
});

// Desktop sidebar store
Alpine.store('sidebar', {
    collapsed: false,
    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebarCollapsed', this.collapsed);
    },
    collapse() {
        this.collapsed = true;
        localStorage.setItem('sidebarCollapsed', 'true');
    },
    expand() {
        this.collapsed = false;
        localStorage.setItem('sidebarCollapsed', 'false');
    },
    init() {
        const saved = localStorage.getItem('sidebarCollapsed');
        if (saved === 'true') {
            this.collapsed = true;
        }
    }
});

// Toast notification store
Alpine.store('toast', {
    items: [],

    init() {
        // Restore toasts from localStorage on page load
        const savedToasts = localStorage.getItem('toasts');
        if (savedToasts) {
            try {
                const toasts = JSON.parse(savedToasts);
                toasts.forEach(toast => {
                    const age = Date.now() - toast.timestamp;
                    if (age < 3000) {
                        this.items.push(toast);
                        setTimeout(() => window.dispatchEvent(new CustomEvent('toast-hide', { detail: toast.id })), 3000 - age);
                    }
                });
                localStorage.removeItem('toasts');
            } catch (e) {
                localStorage.removeItem('toasts');
            }
        }
    },

    add(type, message) {
        const id = Date.now() + Math.random().toString(36).substr(2, 9);
        const toast = { id, type, message, timestamp: Date.now() };
        this.items.unshift(toast);

        // Save to localStorage for persistence across page navigations
        const savedToasts = localStorage.getItem('toasts') || '[]';
        const toasts = JSON.parse(savedToasts);
        toasts.push(toast);
        localStorage.setItem('toasts', JSON.stringify(toasts));

        setTimeout(() => window.dispatchEvent(new CustomEvent('toast-hide', { detail: id })), 3000);
    },

    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
        // Update localStorage
        const savedToasts = localStorage.getItem('toasts') || '[]';
        const toasts = JSON.parse(savedToasts).filter(item => item.id !== id);
        if (toasts.length > 0) {
            localStorage.setItem('toasts', JSON.stringify(toasts));
        } else {
            localStorage.removeItem('toasts');
        }
    },

    success(message) { this.add('success', message); },
    error(message) { this.add('error', message); }
});

// Register chart components
Alpine.data('pieChart', pieChart);
Alpine.data('barChart', barChart);
Alpine.data('dashboard', dashboard);
Alpine.data('categories', categories);
Alpine.data('transactions', transactions);
Alpine.data('transactionModal', transactionModal);
Alpine.data('walletShow', walletShow);
Alpine.data('walletReorder', walletReorder);

// Initialize stores
Alpine.store('theme').init();
Alpine.store('toast').init();

Alpine.start();
