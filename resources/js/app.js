import './bootstrap';

import Alpine from 'alpinejs';
import { pieChart, barChart, dashboard } from './components/chart';

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
        this.isDark = !this.isDark;
        if (this.isDark) {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        }
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

// Register chart components
Alpine.data('pieChart', pieChart);
Alpine.data('barChart', barChart);
Alpine.data('dashboard', dashboard);

// Initialize theme store
Alpine.store('theme').init();

Alpine.start();
