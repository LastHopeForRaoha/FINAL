<?php
if (!defined('ABSPATH')) {
    exit;
}

$member_id = mkwa_get_member_id(get_current_user_id());
$member_stats = mkwa_get_member_stats($member_id);
?>

<div class="mkwa-dashboard">
    <div class="mkwa-dashboard-header">
        <h2><?php _e('Fitness Dashboard', 'mkwa-fitness'); ?></h2>
        <div class="mkwa-level-info">
            <span class="mkwa-level">
                <?php printf(__('Level %d', 'mkwa-fitness'), $member_stats->current_level); ?>
            </span>
            <div class="mkwa-progress-bar">
                <div class="mkwa-progress" style="width: <?php echo $member_stats->level_progress; ?>%"></div>
            </div>
            <span class="mkwa-points">
                <?php echo mkwa_format_points($member_stats->total_points); ?> <?php _e('points', 'mkwa-fitness'); ?>
            </span>
        </div>
    </div>

    <div class="mkwa-stats-grid">
        <div class="mkwa-stat-box">
            <h3><?php _e('Current Streak', 'mkwa-fitness'); ?></h3>
            <div class="mkwa-stat-value">
                <?php echo $member_stats->current_streak; ?> <?php _e('days', 'mkwa-fitness'); ?>
            </div>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('Best Streak', 'mkwa-fitness'); ?></h3>
            <div class="mkwa-stat-value">
                <?php echo $member_stats->best_streak; ?> <?php _e('days', 'mkwa-fitness'); ?>
            </div>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('Total Activities', 'mkwa-fitness'); ?></h3>
            <div class="mkwa-stat-value">
                <?php echo number_format($member_stats->total_activities); ?>
            </div>
        </div>
    </div>

    <div class="mkwa-recent-activities">
        <h3><?php _e('Recent Activities', 'mkwa-fitness'); ?></h3>
        <table class="mkwa-activities-table">
            <thead>
                <tr>
                    <th><?php _e('Date', 'mkwa-fitness'); ?></th>
                    <th><?php _e('Activity', 'mkwa-fitness'); ?></th>
                    <th><?php _e('Points', 'mkwa-fitness'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($member_stats->recent_activities as $activity): ?>
                <tr>
                    <td><?php echo date_i18n(get_option('date_format'), strtotime($activity->created_at)); ?></td>
                    <td><?php echo mkwa_get_activity_label($activity->activity_type); ?></td>
                    <td><?php echo mkwa_format_points($activity->points_earned); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>