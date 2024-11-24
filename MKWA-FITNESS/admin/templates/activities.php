<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap mkwa-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <table class="mkwa-activities-table">
        <thead>
            <tr>
                <th><?php _e('User', 'mkwa-fitness'); ?></th>
                <th><?php _e('Activity', 'mkwa-fitness'); ?></th>
                <th><?php _e('Points', 'mkwa-fitness'); ?></th>
                <th><?php _e('Date', 'mkwa-fitness'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($activities): ?>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?php echo esc_html($activity->display_name); ?></td>
                        <td><?php echo esc_html(mkwa_get_activity_label($activity->activity_type)); ?></td>
                        <td><?php echo esc_html($activity->points); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($activity->logged_at))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4"><?php _e('No activities found.', 'mkwa-fitness'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>