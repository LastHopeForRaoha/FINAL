<?php
if (!defined('ABSPATH')) {
    exit;
}

if (isset($_POST['mkwa_save_settings'])) {
    check_admin_referer('mkwa_settings');
    
    $points_settings = array(
        'mkwa_points_checkin',
        'mkwa_points_class',
        'mkwa_points_cold_plunge',
        'mkwa_points_pr',
        'mkwa_points_competition'
    );

    foreach ($points_settings as $setting) {
        if (isset($_POST[$setting])) {
            update_option($setting, absint($_POST[$setting]));
        }
    }

    if (isset($_POST['mkwa_notification_email_template'])) {
        update_option('mkwa_notification_email_template', wp_kses_post($_POST['mkwa_notification_email_template']));
    }

    echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully!', 'mkwa-fitness') . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('mkwa_settings'); ?>

        <h2 class="title"><?php _e('Points Settings', 'mkwa-fitness'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="mkwa_points_checkin"><?php _e('Check-in Points', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <input type="number" name="mkwa_points_checkin" id="mkwa_points_checkin" 
                           value="<?php echo esc_attr(get_option('mkwa_points_checkin', 3)); ?>" min="0" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mkwa_points_class"><?php _e('Class Points', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <input type="number" name="mkwa_points_class" id="mkwa_points_class" 
                           value="<?php echo esc_attr(get_option('mkwa_points_class', 15)); ?>" min="0" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mkwa_points_cold_plunge"><?php _e('Cold Plunge Points', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <input type="number" name="mkwa_points_cold_plunge" id="mkwa_points_cold_plunge" 
                           value="<?php echo esc_attr(get_option('mkwa_points_cold_plunge', 20)); ?>" min="0" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mkwa_points_pr"><?php _e('Personal Record Points', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <input type="number" name="mkwa_points_pr" id="mkwa_points_pr" 
                           value="<?php echo esc_attr(get_option('mkwa_points_pr', 25)); ?>" min="0" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mkwa_points_competition"><?php _e('Competition Points', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <input type="number" name="mkwa_points_competition" id="mkwa_points_competition" 
                           value="<?php echo esc_attr(get_option('mkwa_points_competition', 50)); ?>" min="0" class="small-text">
                </td>
            </tr>
        </table>

        <h2 class="title"><?php _e('Notification Settings', 'mkwa-fitness'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="mkwa_notification_email_template"><?php _e('Badge Email Template', 'mkwa-fitness'); ?></label>
                </th>
                <td>
                    <textarea name="mkwa_notification_email_template" id="mkwa_notification_email_template" 
                              rows="10" class="large-text"><?php echo esc_textarea(get_option('mkwa_notification_email_template')); ?></textarea>
                    <p class="description">
                        <?php _e('Available variables: {user_name}, {badge_name}, {badge_description}', 'mkwa-fitness'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="mkwa_save_settings" class="button button-primary" 
                   value="<?php esc_attr_e('Save Settings', 'mkwa-fitness'); ?>">
        </p>
    </form>
</div>