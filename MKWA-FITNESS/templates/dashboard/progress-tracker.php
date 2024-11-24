<?php
/**
 * Progress Tracker Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_level = mkwa_calculate_level($member_stats['total_points']);
$next_level_points = mkwa_points_for_next_level($current_level);
$current_level_points = mkwa_points_for_next_level($current_level - 1);
$progress_percentage = min(100, (($member_stats['total_points'] - $current_level_points) / ($next_level_points - $current_level_points)) * 100);
?>

<div class="mkwa-progress-tracker">
    <div class="mkwa-progress-stats">
        <div class="mkwa-stat-block">
            <span class="mkwa-stat-label"><?php esc_html_e('Current Streak', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo esc_html($member_stats['current_streak']); ?> <?php esc_html_e('days', 'mkwa-fitness'); ?></span>
        </div>
        <div class="mkwa-stat-block">
            <span class="mkwa-stat-label"><?php esc_html_e('Best Streak', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo esc_html($member_stats['longest_streak']); ?> <?php esc_html_e('days', 'mkwa-fitness'); ?></span>
        </div>
        <div class="mkwa-stat-block">
            <span class="mkwa-stat-label"><?php esc_html_e('Total Activities', 'mkwa-fitness'); ?></span>
            <span class="mkwa-stat-value"><?php echo esc_html($member_stats['total_activities']); ?></span>
        </div>
    </div>

    <div class="mkwa-level-progress">
        <div class="mkwa-progress-bar">
            <div class="mkwa-progress-fill" style="width: <?php echo esc_attr($progress_percentage); ?>%"></div>
        </div>
        <div class="mkwa-progress-labels">
            <span class="mkwa-progress-start">Level <?php echo esc_html($current_level); ?></span>
            <span class="mkwa-progress-end">Level <?php echo esc_html($current_level + 1); ?></span>
        </div>
        <div class="mkwa-points-needed">
            <?php
            $points_needed = $next_level_points - $member_stats['total_points'];
            echo esc_html(sprintf(
                __('%s points needed for next level', 'mkwa-fitness'),
                mkwa_format_points($points_needed)
            ));
            ?>
        </div>
    </div>
</div>