<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get basic statistics
$total_members = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_members");
$total_activities = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_activities");
$total_points = $wpdb->get_var("SELECT SUM(total_points) FROM {$wpdb->prefix}mkwa_members");

// Get activity breakdown
$activity_breakdown = $wpdb->get_results("
    SELECT activity_type, 
           COUNT(*) as count,
           SUM(points_earned) as total_points
    FROM {$wpdb->prefix}mkwa_activities
    GROUP BY activity_type
    ORDER BY count DESC
");

// Get top members
$top_members = $wpdb->get_results("
    SELECT m.member_id,
           m.total_points,
           m.current_level,
           u.display_name
    FROM {$wpdb->prefix}mkwa_members m
    JOIN {$wpdb->users} u ON m.user_id = u.ID
    ORDER BY m.total_points DESC
    LIMIT 10
");

// Get streak statistics
$streak_stats = $wpdb->get_results("
    SELECT 
        COUNT(CASE WHEN streak_count >= 90 THEN 1 END) as gold_streaks,
        COUNT(CASE WHEN streak_count >= 30 AND streak_count < 90 THEN 1 END) as silver_streaks,
        COUNT(CASE WHEN streak_count >= 7 AND streak_count < 30 THEN 1 END) as bronze_streaks
    FROM {$wpdb->prefix}mkwa_members
");
?>

<div class="mkwa-stats-container">
    <h2><?php _e('Overview', 'mkwa-fitness'); ?></h2>
    
    <div class="mkwa-stats-grid">
        <div class="mkwa-stat-box">
            <h3><?php _e('Total Members', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo number_format($total_members); ?></p>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('Total Activities', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo number_format($total_activities); ?></p>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('Total Points', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo mkwa_format_points($total_points); ?></p>
        </div>
    </div>

    <h2><?php _e('Activity Breakdown', 'mkwa-fitness'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Activity Type', 'mkwa-fitness'); ?></th>
                <th><?php _e('Count', 'mkwa-fitness'); ?></th>
                <th><?php _e('Total Points', 'mkwa-fitness'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activity_breakdown as $activity): ?>
            <tr>
                <td><?php echo mkwa_get_activity_label($activity->activity_type); ?></td>
                <td><?php echo number_format($activity->count); ?></td>
                <td><?php echo mkwa_format_points($activity->total_points); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php _e('Top Members', 'mkwa-fitness'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Rank', 'mkwa-fitness'); ?></th>
                <th><?php _e('Member', 'mkwa-fitness'); ?></th>
                <th><?php _e('Level', 'mkwa-fitness'); ?></th>
                <th><?php _e('Total Points', 'mkwa-fitness'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_members as $index => $member): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo esc_html($member->display_name); ?></td>
                <td><?php echo $member->current_level; ?></td>
                <td><?php echo mkwa_format_points($member->total_points); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php _e('Streak Statistics', 'mkwa-fitness'); ?></h2>
    <div class="mkwa-stats-grid">
        <div class="mkwa-stat-box">
            <h3><?php _e('90+ Day Streaks', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo $streak_stats[0]->gold_streaks; ?></p>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('30+ Day Streaks', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo $streak_stats[0]->silver_streaks; ?></p>
        </div>
        
        <div class="mkwa-stat-box">
            <h3><?php _e('7+ Day Streaks', 'mkwa-fitness'); ?></h3>
            <p class="mkwa-stat-number"><?php echo $streak_stats[0]->bronze_streaks; ?></p>
        </div>
    </div>
</div>