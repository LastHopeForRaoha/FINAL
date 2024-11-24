<form method="post" action="options.php">
    <?php
    settings_fields(MKWA_OPTION_GROUP);
    do_settings_sections(MKWA_SETTINGS_PAGE);
    ?>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Cache Duration', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_cache_duration" 
                       value="<?php echo esc_attr(get_option('mkwa_cache_duration', MKWA_CACHE_DURATION_DEFAULT)); ?>" 
                       min="0" 
                       step="1" /> <?php _e('seconds', 'mkwa-fitness'); ?>
                <p class="description">
                    <?php _e('Duration to cache member data. Set to 0 to disable caching.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>
</form>