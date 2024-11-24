<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mkwa-dashboard-wrapper">
        <!-- Statistics Overview -->
        <div class="mkwa-card">
            <h2><?php _e('Statistics Overview', 'mkwa-fitness'); ?></h2>
            <?php
            $total_users = count(get_users(array('role__in' => array('mkwa_member', 'mkwa_trainer'))));
            $total_activities = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_activity_log");
            $total_badges_awarded = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_user_badges");
            $total_points = $wpdb->get_var("SELECT SUM(points) FROM {$wpdb->prefix}mkwa_activity_log");
            ?>
            <div class="mkwa-stats-grid">
                <div class="mkwa-stat-box">
                    <span class="mkwa-stat-number"><?php echo esc_html($total_users); ?></span>
                    <span class="mkwa-stat-label"><?php _e('Active Members', 'mkwa-fitness'); ?></span>
                </div>
                <div class="mkwa-stat-box">
                    <span class="mkwa-stat-number"><?php echo esc_html($total_activities); ?></span>
                    <span class="mkwa-stat-label"><?php _e('Activities Logged', 'mkwa-fitness'); ?></span>
                </div>
                <div class="mkwa-stat-box">
                    <span class="mkwa-stat-number"><?php echo esc_html($total_badges_awarded); ?></span>
                    <span class="mkwa-stat-label"><?php _e('Badges Awarded', 'mkwa-fitness'); ?></span>
                </div>
                <div class="mkwa-stat-box">
                    <span class="mkwa-stat-number"><?php echo number_format($total_points); ?></span>
                    <span class="mkwa-stat-label"><?php _e('Total Points', 'mkwa-fitness'); ?></span>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="mkwa-card">
            <h2>
                <?php _e('Recent Activities', 'mkwa-fitness'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mkwa-fitness-activities')); ?>" class="page-title-action"><?php _e('View All', 'mkwa-fitness'); ?></a>
            </h2>
            <?php
            $recent_activities = $wpdb->get_results(
                "SELECT a.*, u.display_name 
                FROM {$wpdb->prefix}mkwa_activity_log a 
                JOIN {$wpdb->users} u ON a.user_id = u.ID 
                ORDER BY a.logged_at DESC 
                LIMIT 10"
            );
            
            if ($recent_activities): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('User', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Activity', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Points', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Date', 'mkwa-fitness'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td><?php echo esc_html($activity->display_name); ?></td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $activity->activity_type))); ?></td>
                                <td><?php echo esc_html($activity->points); ?></td>
                                <td><?php 
                                    $date = strtotime($activity->logged_at);
                                    echo esc_html(human_time_diff($date, current_time('timestamp')) . ' ago'); 
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="mkwa-no-data"><?php _e('No activities recorded yet.', 'mkwa-fitness'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Top Performers -->
        <div class="mkwa-card">
            <h2><?php _e('Top Performers', 'mkwa-fitness'); ?></h2>
            <?php
            $top_performers = $wpdb->get_results(
                "SELECT u.ID, u.display_name, 
                    CAST(um.meta_value AS UNSIGNED) as total_points,
                    CAST(um2.meta_value AS UNSIGNED) as current_streak,
                    CAST(um3.meta_value AS UNSIGNED) as longest_streak
                FROM {$wpdb->users} u
                JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mkwa_total_points'
                LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'mkwa_current_streak'
                LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'mkwa_longest_streak'
                WHERE um.meta_value > 0
                ORDER BY total_points DESC
                LIMIT 5"
            );
            
            if ($top_performers): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Rank', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Member', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Points', 'mkwa-fitness'); ?></th>
                            <th><?php _e('Streak', 'mkwa-fitness'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_performers as $index => $performer): ?>
                            <tr>
                                <td><?php echo esc_html($index + 1); ?></td>
                                <td><?php echo esc_html($performer->display_name); ?></td>
                                <td><?php echo number_format($performer->total_points); ?></td>
                                <td>
                                    <?php 
                                    echo esc_html($performer->current_streak); ?> 
                                    <small class="streak-info" title="<?php 
                                        printf(
                                            esc_attr__('Longest streak: %d days', 'mkwa-fitness'), 
                                            $performer->longest_streak
                                        ); 
                                    ?>">
                                        (<?php echo esc_html($performer->longest_streak); ?>)
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="mkwa-no-data"><?php _e('No member activity recorded yet.', 'mkwa-fitness'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>