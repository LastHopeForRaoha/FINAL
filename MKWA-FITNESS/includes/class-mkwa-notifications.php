<?php
/**
 * Notifications management class for MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Notifications {
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
        add_action('wp_ajax_mkwa_mark_notification_read', array($this, 'ajax_mark_notification_read'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'mkwa-notifications',
            MKWA_PLUGIN_URL . 'assets/js/notifications.js',
            array('jquery'),
            MKWA_VERSION,
            true
        );

        wp_localize_script('mkwa-notifications', 'mkwaNotifications', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkwa_notifications')
        ));
    }

    /**
     * AJAX handler for marking notifications as read
     */
    public function ajax_mark_notification_read() {
        check_ajax_referer('mkwa_notifications');

        $notification_id = intval($_POST['notification_id']);
        $user_id = get_current_user_id();

        if (MKWA_Badges::get_instance()->mark_notification_read($notification_id, $user_id)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
}