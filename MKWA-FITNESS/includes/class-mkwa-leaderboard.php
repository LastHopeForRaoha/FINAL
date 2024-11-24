<?php
/**
 * MKWA Fitness Leaderboard functionality
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Leaderboard {
    /**
     * Initialize the leaderboard functionality
     */
    public function __construct() {
        add_action('wp_ajax_mkwa_refresh_leaderboard', array($this, 'refresh_leaderboard_ajax'));
        add_shortcode('mkwa_leaderboard', array($this, 'render_leaderboard_shortcode'));
    }

    /**
     * Get leaderboard data based on type and timeframe
     */
    public function get_leaderboard_data($type = 'overall', $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mkwa_member_stats';
        $current_user_id = get_current_user_id();
        
        switch($type) {
            case 'weekly':
                $where_clause = "WHERE activity_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'monthly':
                $where_clause = "WHERE activity_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'streaks':
                return $wpdb->get_results($wpdb->prepare(
                    "SELECT 
                        ms.user_id,
                        ms.current_streak,
                        ms.total_points,
                        u.display_name,
                        CASE WHEN ms.user_id = %d THEN 1 ELSE 0 END as is_current_user
                    FROM {$table_name} ms
                    JOIN {$wpdb->users} u ON ms.user_id = u.ID
                    ORDER BY ms.current_streak DESC
                    LIMIT %d",
                    $current_user_id,
                    $limit
                ));
            default: // overall
                $where_clause = "";
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                ms.user_id,
                ms.total_points,
                u.display_name,
                CASE WHEN ms.user_id = %d THEN 1 ELSE 0 END as is_current_user
            FROM {$table_name} ms
            JOIN {$wpdb->users} u ON ms.user_id = u.ID
            {$where_clause}
            ORDER BY ms.total_points DESC
            LIMIT %d",
            $current_user_id,
            $limit
        ));
    }

    /**
     * AJAX handler for refreshing leaderboard
     */
    public function refresh_leaderboard_ajax() {
        check_ajax_referer('mkwa-frontend-nonce', 'nonce');
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'overall';
        $leaders = $this->get_leaderboard_data($type);
        
        ob_start();
        $this->render_leaderboard_content($leaders, $type);
        wp_send_json_success(array('html' => ob_get_clean()));
    }

    /**
     * Render leaderboard content
     */
    public function render_leaderboard_content($leaders, $type) {
        include MKWA_PLUGIN_DIR . 'templates/dashboard/leaderboard-content.php';
    }

    /**
     * Shortcode to display leaderboard
     */
    public function render_leaderboard_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'overall',
            'limit' => 10
        ), $atts, 'mkwa_leaderboard');
        
        ob_start();
        include MKWA_PLUGIN_DIR . 'templates/dashboard/leaderboard.php';
        return ob_get_clean();
    }

    /**
     * Display the leaderboard
     */
    public function display_leaderboard($atts) {
        global $mkwa_leaderboard;
        
        if (!isset($mkwa_leaderboard)) {
            return '<p>' . esc_html__('Leaderboard system is not initialized.', 'mkwa-fitness') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'type' => 'overall',
            'limit' => 10
        ), $atts);

        $leaderboard_data = $mkwa_leaderboard->get_leaderboard_data($atts['type'], $atts['limit']);
        
        if (empty($leaderboard_data)) {
            return '<p>' . esc_html__('No leaderboard data available.', 'mkwa-fitness') . '</p>';
        }

        ob_start();
        ?>
        <div class="mkwa-leaderboard">
            <h2><?php echo esc_html(ucfirst($atts['type']) . ' ' . __('Leaderboard', 'mkwa-fitness')); ?></h2>
            <table class="mkwa-leaderboard-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Rank', 'mkwa-fitness'); ?></th>
                        <th><?php esc_html_e('Name', 'mkwa-fitness'); ?></th>
                        <th><?php esc_html_e('Points', 'mkwa-fitness'); ?></th>
                        <th><?php esc_html_e('Score', 'mkwa-fitness'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard_data as $entry): ?>
                    <tr<?php echo ($entry->user_id === get_current_user_id()) ? ' class="current-user"' : ''; ?>>
                        <td><?php echo esc_html($entry->rank); ?></td>
                        <td><?php echo esc_html($entry->display_name); ?></td>
                        <td><?php echo esc_html(number_format($entry->total_points)); ?></td>
                        <td><?php echo esc_html(number_format($entry->ranking_score, 1)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
}