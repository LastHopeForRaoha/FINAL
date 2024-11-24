(function($) {
    'use strict';

    const MKWA_Fitness = {
        init: function() {
            this.initCharts();
            this.initRealTimeUpdates();
            this.initActivityTracking();
            this.initAnimations();
        },

        initCharts: function() {
            const chartElement = document.getElementById('activity-chart');
            if (!chartElement) return;

            // Using Chart.js for activity visualization
            const ctx = chartElement.getContext('2d');
            this.activityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [], // Will be populated with dates
                    datasets: [{
                        label: 'Daily Points',
                        data: [], // Will be populated with points
                        borderColor: '#2271b1',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            this.updateActivityChart();
        },

        updateActivityChart: function() {
            $.ajax({
                url: mkwaFitness.ajaxurl,
                data: {
                    action: 'mkwa_get_activity_data',
                    nonce: mkwaFitness.nonce
                },
                success: (response) => {
                    if (response.success && this.activityChart) {
                        this.activityChart.data.labels = response.data.dates;
                        this.activityChart.data.datasets[0].data = response.data.points;
                        this.activityChart.update();
                    }
                }
            });
        },

        initRealTimeUpdates: function() {
            // Update points and level every minute
            setInterval(() => {
                this.updateUserStats();
            }, 60000);

            // Listen for activity completions
            $(document).on('mkwa_activity_completed', (e, data) => {
                this.handleActivityCompletion(data);
            });
        },

        updateUserStats: function() {
            $.ajax({
                url: mkwaFitness.ajaxurl,
                data: {
                    action: 'mkwa_get_user_stats',
                    nonce: mkwaFitness.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatsDisplay(response.data);
                    }
                }
            });
        },

        updateStatsDisplay: function(stats) {
            $('.mkwa-level').text(`Level ${stats.level}`);
            $('.mkwa-points').text(`${stats.total_points} points`);
            $('.mkwa-progress').css('width', `${stats.level_progress}%`);
            
            // Update other stats
            Object.keys(stats).forEach(key => {
                $(`.mkwa-stat-${key}`).text(stats[key]);
            });
        },

        initActivityTracking: function() {
            // Track activity form submissions
            $('.mkwa-activity-form').on('submit', (e) => {
                e.preventDefault();
                this.submitActivity($(e.currentTarget));
            });
        },

        submitActivity: function($form) {
            const formData = new FormData($form[0]);
            formData.append('action', 'mkwa_submit_activity');
            formData.append('nonce', mkwaFitness.nonce);

            $.ajax({
                url: mkwaFitness.ajaxurl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.handleActivityCompletion(response.data);
                        this.showNotification(response.data.message);
                    }
                }
            });
        },

        handleActivityCompletion: function(data) {
            // Update stats
            this.updateUserStats();
            
            // Update chart if available
            if (this.activityChart) {
                this.updateActivityChart();
            }

            // Show achievement notification if any
            if (data.achievements) {
                data.achievements.forEach(achievement => {
                    this.showAchievementNotification(achievement);
                });
            }
        },

        showNotification: function(message, type = 'success') {
            const notification = $(`
                <div class="mkwa-notification mkwa-notification-${type}">
                    ${message}
                </div>
            `).appendTo('body');

            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 3000);
        },

        showAchievementNotification: function(achievement) {
            const notification = $(`
                <div class="mkwa-achievement-notification">
                    <div class="mkwa-achievement-icon">
                        <img src="${achievement.icon}" alt="${achievement.title}">
                    </div>
                    <div class="mkwa-achievement-content">
                        <h4>${achievement.title}</h4>
                        <p>${achievement.description}</p>
                    </div>
                </div>
            `).appendTo('body');

            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 5000);
        },

        initAnimations: function() {
            // Animate stats when they come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('mkwa-animate');
                    }
                });
            });

            document.querySelectorAll('.mkwa-stat-box').forEach(box => {
                observer.observe(box);
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        MKWA_Fitness.init();
    });

})(jQuery);