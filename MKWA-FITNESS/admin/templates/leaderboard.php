<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mkwa-leaderboard-wrapper">
        <h2><?php _e('Leaderboard', 'mkwa-fitness'); ?></h2>
        <?php
        global $wpdb;
        $leaderboard = $wpdb->get_results(
            "SELECT u.ID, u.display_name, 
                CAST(um.meta_value AS UNSIGNED) as total_points
            FROM {$wpdb->users} u
            JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'mkwa_total_points'
            WHERE um.meta_value > 0
            ORDER BY total_points DESC
            LIMIT 10"
        );

        if ($leaderboard): ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Rank', 'mkwa-fitness'); ?></th>
                        <th><?php _e('Member', 'mkwa-fitness'); ?></th>
                        <th><?php _e('Points', 'mkwa-fitness'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $member): ?>
                        <tr>
                            <td><?php echo esc_html($index + 1); ?></td>
                            <td><?php echo esc_html($member->display_name); ?></td>
                            <td><?php echo number_format($member->total_points); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No leaderboard data available.', 'mkwa-fitness'); ?></p>
        <?php endif; ?>
    </div>
</div>