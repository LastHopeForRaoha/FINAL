jQuery(document).ready(function($) {
    // Media uploader for badge icons
    var mediaUploader;
    
    $('#upload_icon_button').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Choose Badge Icon',
            button: {
                text: 'Use this icon'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#icon_url').val(attachment.url);
            $('#icon_preview').attr('src', attachment.url).show();
            
            // Trigger AJAX to save the attachment ID
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mkwa_upload_badge_icon',
                    attachment_id: attachment.id,
                    _ajax_nonce: mkwaAdmin.nonce
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to process icon upload');
                    }
                }
            });
        });

        mediaUploader.open();
    });

    // Preview existing icon URL
    $('#icon_url').on('change', function() {
        var url = $(this).val();
        if (url) {
            $('#icon_preview').attr('src', url).show();
        } else {
            $('#icon_preview').hide();
        }
    });

    // Mark notifications as read
    $('.mkwa-notification .mark-read').on('click', function(e) {
        e.preventDefault();
        var $notification = $(this).closest('.mkwa-notification');
        var notificationId = $notification.data('notification-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mkwa_mark_notification_read',
                notification_id: notificationId,
                _ajax_nonce: mkwaAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $notification.fadeOut();
                }
            }
        });
    });

    // Form validation
    $('#mkwa-badge-form').on('submit', function(e) {
        var $required = $(this).find('[required]');
        var valid = true;

        $required.each(function() {
            if (!$(this).val()) {
                valid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Function to update progress bars
    function updateProgressBars() {
        $('.progress-bar .progress').each(function() {
            var $this = $(this);
            var percentage = $this.data('percentage');
            $this.css('width', percentage + '%');
        });
    }

    // Call the function to update progress bars on page load
    updateProgressBars();

    // Add event listener for badge animation (if needed)
    $('.animated-badge').on('mouseenter', function() {
        $(this).addClass('sparkle');
    }).on('mouseleave', function() {
        $(this).removeClass('sparkle');
    });
});