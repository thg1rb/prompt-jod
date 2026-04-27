import './bootstrap';

import Alpine from 'alpinejs';
import { pieChart, barChart, dashboard } from './components/chart';

window.Alpine = Alpine;

// Register chart components
Alpine.data('pieChart', pieChart);
Alpine.data('barChart', barChart);
Alpine.data('dashboard', dashboard);

Alpine.start();
