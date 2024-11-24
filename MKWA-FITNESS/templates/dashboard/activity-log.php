<?php
/**
 * Activity Log Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$activities = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}mkwa_activity_log 
    WHERE user_id = %d 
    ORDER BY logged_at DESC 
    LIMIT 10",
    $user_id
));
?>

<div class="mkwa-activity-log">
    <?php if ($activities) : ?>
        <ul class="mkwa-activity-list">
            <?php foreach ($activities as $activity) : ?>
                <li class="mkwa-activity-item">
                    <span class="mkwa-activity-type">
                        <?php echo esc_html(mkwa_get_activity_label($activity->activity_type)); ?>
                    </span>
                    <span class="mkwa-activity-points">
                        +<?php echo esc_html($activity->points); ?> <?php esc_html_e('points', 'mkwa-fitness'); ?>
                    </span>
                    <span class="mkwa-activity-time">
                        <?php echo esc_html(human_time_diff(strtotime($activity->logged_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'mkwa-fitness'); ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p class="mkwa-no-activities">
            <?php esc_html_e('No activities recorded yet. Start working out to earn points!', 'mkwa-fitness'); ?>
        </p>
    <?php endif; ?>
</div>