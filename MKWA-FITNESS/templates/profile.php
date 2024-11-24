<?php
if (!defined('ABSPATH')) {
    exit;
}

$member_id = mkwa_get_member_id(get_current_user_id());
$member_stats = mkwa_get_member_stats($member_id);
?>

<div class="mkwa-profile">
    <div class="mkwa-profile-header">
        <div class="mkwa-avatar">
            <?php echo get_avatar(get_current_user_id(), 96); ?>
        </div>
        <div class="mkwa-profile-info">
            <h2><?php echo esc_html(wp_get_current_user()->display_name); ?></h2>
            <div class="mkwa-level-badge">
                <?php printf(__('Level %d', 'mkwa-fitness'), $member_stats->current_level); ?>
            </div>
        </div>
    </div>

    <div class="mkwa-profile-stats">
        <div class="mkwa-stat-item">
            <span class="mkwa-stat-label"><?php _e('Total Points', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo mkwa_format_points($member_stats->total_points); ?></span>
        </div>
        
        <div class="mkwa-stat-item">
            <span class="mkwa-stat-label"><?php _e('Current Streak', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo $member_stats->current_streak; ?> <?php _e('days', 'mkwa-fitness'); ?></span>
        </div>
        
        <div class="mkwa-stat-item">
            <span class="mkwa-stat-label"><?php _e('Activities', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo number_format($member_stats->total_activities); ?></span>
        </div>
    </div>

    <div class="mkwa-activity-history">
        <h3><?php _e('Activity History', 'mkwa-fitness'); ?></h3>
        <div class="mkwa-activity-chart">
            <!-- Activity chart will be rendered here by JavaScript -->
        </div>
    </div>
</div>