jQuery(document).ready(function($) {
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
});