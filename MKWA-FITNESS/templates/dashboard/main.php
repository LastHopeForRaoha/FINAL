<?php
/**
 * Main Dashboard Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

$member_stats = mkwa_get_member_stats($member_id);
$user_data = get_userdata($user_id);
?>

<div class="mkwa-dashboard">
    <div class="mkwa-dashboard-header">
        <h2><?php echo esc_html(sprintf(__('Welcome, %s!', 'mkwa-fitness'), $user_data->display_name)); ?></h2>
        <div class="mkwa-level-info">
            <span class="mkwa-level">
                <?php echo esc_html(sprintf(__('Level %d', 'mkwa-fitness'), mkwa_calculate_level($member_stats['total_points']))); ?>
            </span>
            <span class="mkwa-points">
                <?php echo esc_html(sprintf(__('%s points', 'mkwa-fitness'), mkwa_format_points($member_stats['total_points']))); ?>
            </span>
        </div>
    </div>

    <div class="mkwa-dashboard-grid">
        <!-- Progress Section -->
        <div class="mkwa-dashboard-section mkwa-progress">
            <h3><?php esc_html_e('Your Progress', 'mkwa-fitness'); ?></h3>
            <?php include MKWA_PLUGIN_DIR . 'templates/dashboard/progress-tracker.php'; ?>
        </div>

        <!-- Recent Activity Section -->
        <div class="mkwa-dashboard-section mkwa-activity">
            <h3><?php esc_html_e('Recent Activity', 'mkwa-fitness'); ?></h3>
            <?php include MKWA_PLUGIN_DIR . 'templates/dashboard/activity-log.php'; ?>
        </div>

        <!-- Badges Section -->
        <div class="mkwa-dashboard-section mkwa-badges">
            <h3><?php esc_html_e('Your Badges', 'mkwa-fitness'); ?></h3>
            <?php include MKWA_PLUGIN_DIR . 'templates/dashboard/badges.php'; ?>
        </div>

        <!-- Leaderboard Section -->
<div class="mkwa-dashboard-section mkwa-leaderboard">
    <h3><?php esc_html_e('Leaderboard', 'mkwa-fitness'); ?></h3>
    <?php echo do_shortcode('[mkwa_leaderboard limit="5"]'); ?>
</div>
    </div>
</div>