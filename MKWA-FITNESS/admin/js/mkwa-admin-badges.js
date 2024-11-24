jQuery(document).ready(function($) {
    // Badge icon upload
    $('.mkwa-upload-badge-icon').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var customUploader = wp.media({
            title: 'Choose Badge Icon',
            library: {
                type: 'image'
            },
            button: {
                text: 'Select Icon'
            },
            multiple: false
        });

        customUploader.on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            button.siblings('#badge_icon_url').val(attachment.url);
            button.siblings('.mkwa-badge-icon-preview').html('<img src="' + attachment.url + '" alt="">');
        });

        customUploader.open();
    });
});