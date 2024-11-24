<?php
/**
 * Badge management class for MKWA Fitness
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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_mkwa_upload_badge_icon', array($this, 'handle_icon_upload'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('mkwa-fitness_page_mkwa-badges' !== $hook) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'mkwa-badge-admin',
            MKWA_PLUGIN_URL . 'admin/js/badge-admin.js',
            array('jquery'),
            MKWA_VERSION,
            true
        );
        wp_enqueue_style(
            'mkwa-badge-admin',
            MKWA_PLUGIN_URL . 'admin/css/badge-admin.css',
            array(),
            MKWA_VERSION
        );
    }

    /**
     * Handle badge icon upload via AJAX
     */
    public function handle_icon_upload() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_ajax_referer('mkwa_upload_badge_icon', 'nonce');

        $attachment_id = intval($_POST['attachment_id']);
        $attachment_url = wp_get_attachment_url($attachment_id);

        if ($attachment_url) {
            wp_send_json_success(array('url' => $attachment_url));
        } else {
            wp_send_json_error('Failed to get attachment URL');
        }
    }

    /**
     * Process badge icon
     */
    private function process_badge_icon($icon_url) {
        // Validate URL
        if (!filter_var($icon_url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check if the URL is from the same domain
        $site_url = get_site_url();
        if (strpos($icon_url, $site_url) !== 0) {
            return false;
        }

        // Get file extension
        $file_info = wp_check_filetype($icon_url);
        if (!in_array($file_info['ext'], array('jpg', 'jpeg', 'png', 'gif'))) {
            return false;
        }

        return $icon_url;
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            'Manage Badges',
            'Badges',
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
     * Send badge notification
     */
    private function send_badge_notification($user_id, $badge_id) {
        global $wpdb;
        
        // Get badge details
        $badge = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkwa_badges WHERE id = %d",
            $badge_id
        ));

        if (!$badge) {
            return false;
        }

        // Create notification
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

        // Send email notification
        $user = get_userdata($user_id);
        if ($user) {
            $this->send_badge_email($user, $badge);
        }

        return true;
    }

    /**
     * Send badge email
     */
    private function send_badge_email($user, $badge) {
        $subject = sprintf(__('Congratulations! You\'ve earned the %s badge!', 'mkwa-fitness'), $badge->title);
        
        $message = get_option('mkwa_notification_email_template');
        $message = str_replace(
            array('{user_name}', '{badge_name}', '{badge_description}'),
            array($user->display_name, $badge->title, $badge->description),
            $message
        );

        wp_mail($user->user_email, $subject, $message);
    }

    /**
     * Get user's badges
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
}