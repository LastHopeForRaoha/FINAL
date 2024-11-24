jQuery(document).ready(function($) {
    // Existing activity log and progress tracking code
    function refreshActivityLog() {
        $.ajax({
            url: mkwaAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_refresh_activity',
                nonce: mkwaAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.mkwa-activity-log').html(response.data.html);
                }
            }
        });
    }

    function refreshProgress() {
        $.ajax({
            url: mkwaAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_refresh_progress',
                nonce: mkwaAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.mkwa-progress-tracker').html(response.data.html);
                    updateDashboardHeader(response.data.stats);
                }
            }
        });
    }

    function updateDashboardHeader(stats) {
        $('.mkwa-level').text('Level ' + stats.current_level);
        $('.mkwa-points').text(stats.total_points + ' points');
    }

    function logActivity(activityType) {
        $.ajax({
            url: mkwaAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_log_activity',
                nonce: mkwaAjax.nonce,
                activity_type: activityType
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data.message);
                    refreshActivityLog();
                    refreshProgress();
                } else {
                    showNotification('error', response.data.message);
                }
            }
        });
    }

    // Class registration functionality
    function registerForClass(classId) {
        $.ajax({
            url: mkwaAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_register_for_class',
                nonce: mkwaAjax.nonce,
                class_id: classId
            },
            beforeSend: function() {
                $('.mkwa-btn-register[data-class-id="' + classId + '"]')
                    .prop('disabled', true)
                    .text(mkwaStrings.registering);
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data.message);
                    refreshClassCard(classId);
                    refreshProgress(); // Update points if applicable
                } else {
                    showNotification('error', response.data.message);
                    $('.mkwa-btn-register[data-class-id="' + classId + '"]')
                        .prop('disabled', false)
                        .text(mkwaStrings.register);
                }
            },
            error: function() {
                showNotification('error', mkwaStrings.errorOccurred);
                $('.mkwa-btn-register[data-class-id="' + classId + '"]')
                    .prop('disabled', false)
                    .text(mkwaStrings.register);
            }
        });
    }

    function unregisterFromClass(classId) {
        if (confirm(mkwaStrings.confirmCancel)) {
            $.ajax({
                url: mkwaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'mkwa_unregister_from_class',
                    nonce: mkwaAjax.nonce,
                    class_id: classId
                },
                beforeSend: function() {
                    $('.mkwa-btn-cancel[data-class-id="' + classId + '"]')
                        .prop('disabled', true)
                        .text(mkwaStrings.cancelling);
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                        refreshClassCard(classId);
                    } else {
                        showNotification('error', response.data.message);
                        $('.mkwa-btn-cancel[data-class-id="' + classId + '"]')
                            .prop('disabled', false)
                            .text(mkwaStrings.cancelRegistration);
                    }
                },
                error: function() {
                    showNotification('error', mkwaStrings.errorOccurred);
                    $('.mkwa-btn-cancel[data-class-id="' + classId + '"]')
                        .prop('disabled', false)
                        .text(mkwaStrings.cancelRegistration);
                }
            });
        }
    }

    function refreshClassCard(classId) {
        $.ajax({
            url: mkwaAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_refresh_class_card',
                nonce: mkwaAjax.nonce,
                class_id: classId
            },
            success: function(response) {
                if (response.success) {
                    $('.mkwa-class-card[data-class-id="' + classId + '"]')
                        .replaceWith(response.data.html);
                }
            }
        });
    }

    // Class filter functionality
    $('.mkwa-filter-btn').on('click', function() {
        const filter = $(this).data('filter');
        
        $('.mkwa-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('.mkwa-class-card').show();
        } else {
            $('.mkwa-class-card').hide();
            $('.mkwa-class-card.' + filter).show();
        }
    });

    // Register button click handler
    $(document).on('click', '.mkwa-btn-register', function(e) {
        e.preventDefault();
        const classId = $(this).data('class-id');
        registerForClass(classId);
    });

    // Cancel registration button click handler
    $(document).on('click', '.mkwa-btn-cancel', function(e) {
        e.preventDefault();
        const classId = $(this).data('class-id');
        unregisterFromClass(classId);
    });

    // Activity logging button click handler
    $('.mkwa-log-activity-btn').on('click', function(e) {
        e.preventDefault();
        const activityType = $(this).data('activity-type');
        logActivity(activityType);
    });

    // Notification system
    function showNotification(type, message) {
        const notification = $('<div>')
            .addClass('mkwa-notification')
            .addClass('mkwa-notification-' + type)
            .text(message);

        $('body').append(notification);

        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Set up periodic refreshes
    setInterval(refreshActivityLog, 300000); // 5 minutes
    setInterval(refreshProgress, 300000);
});