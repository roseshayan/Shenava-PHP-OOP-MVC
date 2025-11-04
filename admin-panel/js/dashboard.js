/**
 * Shenava - Dashboard Specific JavaScript
 */

class Dashboard {
    constructor() {
        this.initCharts();
        this.setupRealTimeUpdates();
    }

    initCharts() {
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            this.initUsersChart();
            this.initPlaysChart();
        }
    }

    initUsersChart() {
        const ctx = document.getElementById('usersChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور'],
                    datasets: [{
                        label: 'کاربران جدید',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: '#00BFA5',
                        backgroundColor: 'rgba(0, 191, 165, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    initPlaysChart() {
        const ctx = document.getElementById('playsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
                    datasets: [{
                        label: 'تعداد پخش',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        backgroundColor: '#FF7043'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }

    setupRealTimeUpdates() {
        // Update stats every 30 seconds
        setInterval(() => {
            this.updateStats();
        }, 30000);
    }

    updateStats() {
        $.ajax({
            url: 'includes/get-stats.php',
            method: 'GET',
            success: (data) => {
                if (data.success) {
                    this.updateStatCards(data.stats);
                }
            }
        });
    }

    updateStatCards(stats) {
        Object.keys(stats).forEach(key => {
            const element = $(`[data-stat="${key}"]`);
            if (element.length) {
                // Animate counter
                this.animateCounter(element[0], stats[key]);
            }
        });
    }

    animateCounter(element, target) {
        let current = parseInt(element.textContent.replace(/,/g, ''));
        const increment = target > current ? 1 : -1;
        const stepTime = Math.abs(Math.floor(1000 / (target - current)));

        const timer = setInterval(() => {
            current += increment;
            element.textContent = current.toLocaleString();

            if (current === target) {
                clearInterval(timer);
            }
        }, stepTime);
    }
}

// Initialize dashboard
$(document).ready(function () {
    if ($('body').hasClass('dashboard-page')) {
        window.dashboard = new Dashboard();
    }
});