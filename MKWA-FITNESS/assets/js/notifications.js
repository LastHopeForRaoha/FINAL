jQuery(document).ready(function($) {
    $('.mkwa-notification').on('click', '.mkwa-mark-read', function(e) {
        e.preventDefault();
        
        var $notification = $(this).closest('.mkwa-notification');
        var notificationId = $notification.data('notification-id');

        $.ajax({
            url: mkwaNotifications.ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_mark_notification_read',
                notification_id: notificationId,
                _ajax_nonce: mkwaNotifications.nonce
            },
            success: function(response) {
                if (response.success) {
                    $notification.fadeOut();
                }
            }
        });
    });
});