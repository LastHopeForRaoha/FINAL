<?php
/**
 * Badge management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Badges {
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('mkwa_after_activity_logged', array($this, 'check_activity_achievements'), 10, 3);
        add_action('mkwa_points_updated', array($this, 'check_points_achievements'), 10, 2);
        add_action('mkwa_season_changed', array($this, 'check_seasonal_achievements'), 10, 2);
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_default_badges'));
        add_action('wp_ajax_mkwa_upload_badge_icon', array($this, 'handle_icon_upload'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            __('Manage Badges', 'mkwa-fitness'),
            __('Badges', 'mkwa-fitness'),
            'manage_options',
            'mkwa-badges',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Create a new badge
     */
    public function create_badge($data) {
        global $wpdb;

        $defaults = array(
            'title' => '',
            'description' => '',
            'icon_url' => '',
            'badge_type' => 'standard',
            'category' => 'fitness',
            'points_required' => 0,
            'activities_required' => '',
            'cultural_requirement' => '',
            'seasonal_requirement' => ''
        );

        $data = wp_parse_args($data, $defaults);
        
        // Validate icon URL
        if (!empty($data['icon_url'])) {
            $icon_url = $this->process_badge_icon($data['icon_url']);
            if (!$icon_url) {
                return false;
            }
            $data['icon_url'] = $icon_url;
        }

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'mkwa_badges',
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );

        if ($inserted) {
            do_action('mkwa_badge_created', $wpdb->insert_id, $data);
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Award a badge to a user
     */
    public function award_badge($user_id, $badge_id) {
        global $wpdb;

        // Check if already awarded
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mkwa_user_badges 
            WHERE user_id = %d AND badge_id = %d",
            $user_id,
            $badge_id
        ));

        if ($existing) {
            return false;
        }

        $awarded = $wpdb->insert(
            $wpdb->prefix . 'mkwa_user_badges',
            array(
                'user_id' => $user_id,
                'badge_id' => $badge_id,
                'earned_date' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );

        if ($awarded) {
            do_action('mkwa_badge_awarded', $badge_id, $user_id);
            $this->send_badge_notification($user_id, $badge_id);
            return true;
        }

        return false;
    }

    /**
     * Get user badges
     */
    public function get_user_badges($user_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, ub.earned_date 
            FROM {$wpdb->prefix}mkwa_badges b 
            JOIN {$wpdb->prefix}mkwa_user_badges ub ON b.id = ub.badge_id 
            WHERE ub.user_id = %d 
            ORDER BY ub.earned_date DESC",
            $user_id
        ), ARRAY_A);
    }

    /**
     * Check activity achievements
     */
    public function check_activity_achievements($user_id, $activity_type, $activity_data) {
        global $wpdb;

        $badges = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}mkwa_badges 
            WHERE activities_required != ''",
            ARRAY_A
        );

        foreach ($badges as $badge) {
            $requirements = json_decode($badge['activities_required'], true);
            if ($this->check_activity_requirements($user_id, $requirements)) {
                $this->award_badge($user_id, $badge['id']);
            }
        }
    }

    /**
     * Send badge notification
     */
    private function send_badge_notification($user_id, $badge_id) {
        $badge = $this->get_badge($badge_id);
        if (!$badge) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Store notification
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'mkwa_notifications',
            array(
                'user_id' => $user_id,
                'badge_id' => $badge_id,
                'type' => 'badge_earned',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );

        // Send email
        $subject = sprintf(__('Congratulations! You\'ve earned the %s badge!', 'mkwa-fitness'), $badge['title']);
        
        $message = strtr(
            get_option('mkwa_notification_email_template'),
            array(
                '{user_name}' => $user->display_name,
                '{badge_name}' => $badge['title'],
                '{badge_description}' => $badge['description']
            )
        );

        wp_mail($user->user_email, $subject, $message);

        return true;
    }

    // Add more methods as needed...
}