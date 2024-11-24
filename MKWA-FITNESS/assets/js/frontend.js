(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        const MKWA = {
            init: function() {
                this.bindEvents();
                this.initializeTooltips();
            },

            bindEvents: function() {
                $('.mkwa-activity-button').on('click', this.handleActivityLogging.bind(this));
            },

            initializeTooltips: function() {
                // Initialize tooltips if you're using them for badges
                $('.mkwa-badge img').each(function() {
                    $(this).tooltip({
                        title: $(this).data('description'),
                        placement: 'top'
                    });
                });
            },

            handleActivityLogging: function(e) {
                e.preventDefault();
                const $button = $(e.currentTarget);
                const activityType = $button.data('activity');

                // Disable button during processing
                $button.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: mkwaAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mkwa_log_activity',
                        activity_type: activityType,
                        nonce: mkwaAjax.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showNotification('success', response.data.message);
                            this.updateStats(response.data);
                        } else {
                            this.showNotification('error', response.data);
                        }
                    },
                    error: () => {
                        this.showNotification('error', 'An error occurred. Please try again.');
                    },
                    complete: () => {
                        // Re-enable button
                        $button.prop('disabled', false);
                    }
                });
            },

            updateStats: function(data) {
                // Update total points
                $('.mkwa-stat-number.total-points').text(data.total_points);

                // Update other stats if needed
                if (data.current_streak) {
                    $('.mkwa-stat-number.current-streak').text(data.current_streak);
                }
            },

            showNotification: function(type, message) {
                const $notification = $('<div>', {
                    class: `mkwa-notification ${type}`,
                    text: message
                });

                $('body').append($notification);

                // Remove notification after 3 seconds
                setTimeout(() => {
                    $notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        };

        // Initialize the application
        MKWA.init();
    });

})(jQuery);